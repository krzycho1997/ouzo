<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Command;

use Ouzo\Config;
use Ouzo\Utilities\Clock;
use Ouzo\Utilities\Path;
use Ouzo\Utilities\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationGeneratorCommand extends Command
{
    /* @var InputInterface */
    private $input;
    /* @var OutputInterface */
    private $output;

    public function configure()
    {
        $this->setName('migration:generate')
            ->addArgument('name', InputArgument::REQUIRED, 'Migration name')
            ->addArgument('dir', InputArgument::OPTIONAL, 'Migration directory');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $name = $this->input->getArgument('name');
        $dir = $this->input->getArgument('dir');

        $pathFromConfig = Config::getValue('migrations', 'dir');

        $name = Strings::underscoreToCamelCase($name);
        $dir = $dir ?: Path::join(ROOT_PATH, $pathFromConfig);
        $clock = Clock::now();
        $date = $clock->format('YmdHis');

        $filename = "{$date}_{$name}.php";
        $path = Path::join($dir, $filename);

        $this->output->writeln("Migration file name: <info>{$path}</info>");

        $data = <<<MIGRATION
<?php

use Ouzo\Db;
use Ouzo\Migration\Migration;

class {$name} extends Migration
{
    public function run(Db \$db)
    {
        \$db->execute("SELECT 1");
    }
}

MIGRATION;

        file_put_contents($path, $data);

        $this->output->writeln("<comment>Generating...</comment> <info>DONE</info>");

        return 0;
    }
}
