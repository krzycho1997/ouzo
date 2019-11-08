<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Command;

use Ouzo\Config;
use Ouzo\Db\TransactionalProxy;
use Ouzo\Migration\MigrationDbConfig;
use Ouzo\Migration\MigrationFailedException;
use Ouzo\Migration\MigrationInitializer;
use Ouzo\Migration\MigrationLoader;
use Ouzo\Migration\MigrationProgressBar;
use Ouzo\Migration\MigrationRunner;
use Ouzo\Utilities\Objects;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MigrationRunnerCommand extends Command
{
    /* @var InputInterface */
    private $input;
    /* @var OutputInterface */
    private $output;
    /* @var bool */
    private $commitEarly;
    /* @var bool */
    private $force;
    /* @var bool */
    private $init;
    /* @var string[] */
    private $dirs;
    /* @var bool */
    private $reset;
    /* @var MigrationDbConfig */
    private $dbConfig;
    /* @var bool */
    private $noAnimations;

    public function configure()
    {
        $this->setName('migration:run')
            ->addOption('commit_early', 'c', InputOption::VALUE_NONE, 'Commit after each migration')
            ->addOption('reset', 'r', InputOption::VALUE_NONE, 'Remove all previous migrations')
            ->addOption('init', 'i', InputOption::VALUE_NONE, 'Add schema_migrations table')
            ->addOption('dir', 'd', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Directories with migrations (separated by comma)')
            ->addOption('db_name', 'N', InputOption::VALUE_REQUIRED, 'Database name')
            ->addOption('db_user', 'U', InputOption::VALUE_REQUIRED, 'Database user')
            ->addOption('db_pass', 'S', InputOption::VALUE_REQUIRED, 'Database password')
            ->addOption('db_host', 'H', InputOption::VALUE_REQUIRED, 'Database host')
            ->addOption('db_port', 'P', InputOption::VALUE_REQUIRED, 'Database port')
            ->addOption('db_driver', 'D', InputOption::VALUE_REQUIRED, 'Database driver')
            ->addOption('no_animations', 'a', InputOption::VALUE_NONE, 'Disable animations (e.g. progress bar)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force confirmation');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->commitEarly = $this->input->getOption('commit_early');
        $this->force = $this->input->getOption('force');
        $this->init = $this->input->getOption('init');
        $this->dirs = $this->input->getOption('dir') ?: ['.'];
        $this->reset = $this->input->getOption('reset');
        $this->noAnimations = $this->input->getOption('no_animations');
        $this->dbConfig = new MigrationDbConfig($input);

        $this->migrate();
    }

    private function migrate()
    {
        Config::overrideProperty('db')->with($this->dbConfig->toArray());

        $this->output->writeln('=======================================================');
        $this->output->writeln("  Database = " . $this->dbConfig);
        $this->output->writeln("  Commit early = " . Objects::toString($this->commitEarly));
        $this->output->writeln("  Directory = " . Objects::toString($this->dirs));
        $this->output->writeln("  Initialize = " . Objects::toString($this->commitEarly));
        $this->output->writeln("  Force = " . Objects::toString($this->init));
        $this->output->writeln("  Reset = " . Objects::toString($this->reset));
        $this->output->writeln("  No animations = " . Objects::toString($this->noAnimations));
        $this->output->writeln('=======================================================');
        $this->output->writeln('');

        $initializer = new MigrationInitializer($this->output);
        $loader = new MigrationLoader();
        $runner = new MigrationRunner();

        $db = $initializer->connectToDatabase();
        if ($this->init && $this->reset) {
            $initializer->dropMigrations($db);
        }
        if ($this->init) {
            $initializer->initMigrations($db);
        }
        if ($this->reset) {
            $initializer->resetMigrations();
        }

        $this->output->writeln("\nMigrations to apply:");
        $migrations = ($loader)->loadMigrations($this->dirs);
        foreach ($migrations as $version => $className) {
            $this->output->writeln(" [$version] $className");
        }
        $this->output->writeln('');

        if (empty($migrations)) {
            $this->output->writeln('None. Bye!');
            return 0;
        }

        if (!$this->force) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Do you want to continue? [y/n] ', false);
            $question->setMaxAttempts(1);
            if (!$helper->ask($this->input, $this->output, $question)) {
                $this->output->writeln('What a bummer. Bye!');
                return 0;
            }
        }

        $progressBar = $this->createProgressBar(count($migrations));
        try {
            $runner = $this->commitEarly ? $runner : TransactionalProxy::newInstance($runner);
            $runner->runAll($db, $progressBar, $migrations);
            $this->output->writeln("\n\n<info>That's all. Bye!</info>");
            return 0;
        } catch (MigrationFailedException $ex) {
            $this->output->writeln("\n<error>Error</error>");
            $this->output->writeln("Could not apply migration {$ex->getClassName()} version {$ex->getVersion()}: {$ex->getMessage()}");
            $this->output->writeln($ex->getPrevious()->getTraceAsString());
            return 1;
        }
    }

    private function createProgressBar(int $max): MigrationProgressBar
    {
        return $this->noAnimations ? MigrationProgressBar::empty() : MigrationProgressBar::create($this->output, $max);
    }
}