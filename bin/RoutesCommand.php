<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Command;

use Ouzo\ApplicationPaths;
use Ouzo\Config;
use Ouzo\Injection\Annotation\Inject;
use Ouzo\Routing\Generator\RouteFileGenerator;
use Ouzo\Routing\Route;
use Ouzo\Routing\RouteRule;
use Ouzo\Uri\Es6UriHelperGenerator;
use Ouzo\Uri\JsUriHelperGenerator;
use Ouzo\Uri\UriHelperGenerator;
use Ouzo\Utilities\Arrays;
use Ouzo\Utilities\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RoutesCommand extends Command
{
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;

    #[Inject]
    private RouteFileGenerator $routeFileGenerator;

    public function configure()
    {
        $this->setName('ouzo:routes')
            ->addOption('controller', 'c', InputOption::VALUE_OPTIONAL)
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path for JS helper generated file', Path::join(ROOT_PATH, 'public'))
            ->addOption('generate-all', 'a', InputOption::VALUE_NONE)
            ->addOption('generate-php', 'g', InputOption::VALUE_NONE)
            ->addOption('generate-js', 'j', InputOption::VALUE_NONE)
            ->addOption('generate-es6', 'e', InputOption::VALUE_NONE);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $generateOptionFunctionMap = [
            "generate-php" => "generatePhpHelper",
            "generate-js" => "generateJsHelper",
            "generate-es6" => "generateEs6Helper",
            "generate-all" => "generateAllHelpers",
        ];
        $selectedOptions = array_filter($input->getOptions());
        $selectedGeneratedOptions = array_intersect(array_keys($selectedOptions), array_keys($generateOptionFunctionMap));

        $this->generateRoutes();
        if (sizeof($selectedGeneratedOptions)) {
            $this->runSelectedGenerators($selectedGeneratedOptions, $generateOptionFunctionMap);
        } else {
            if ($input->getOption('controller')) {
                $this->controller();
            } else {
                $this->all();
            }
        }

        return 0;
    }

    private function generateRoutes()
    {
        $destination = Config::getValue('app', 'routes', 'destination') ?? Path::join(ROOT_PATH, 'config', 'generated_routes.php');
        $resources = Config::getValue('app', 'routes', 'resources') ?? [];
        if ($this->routeFileGenerator->generate($destination, $resources) !== false) {
            $this->output->writeln("File with routes is generated in <info>$destination</info>");
        }
    }

    private function runSelectedGenerators($selectedGeneratedOptions, $generateOptionFunctionMap)
    {
        foreach ($generateOptionFunctionMap as $optionName => $functionName) {
            if (in_array($optionName, $selectedGeneratedOptions)) {
                call_user_func([$this, $functionName]);
            }
        }
    }

    public function generateAllHelpers()
    {
        $this->generatePhpHelper();
        $this->generateJsHelper();
        $this->generateEs6Helper();
    }

    private function generatePhpHelper()
    {
        $routesPhpHelperPath = Path::join(ROOT_PATH, ApplicationPaths::getHelperPath(), 'GeneratedUriHelper.php');
        if (UriHelperGenerator::generate()->saveToFile($routesPhpHelperPath) !== false) {
            $this->output->writeln("File with PHP uri helpers is generated in <info>$routesPhpHelperPath</info>");
        }
    }

    private function generateJsHelper()
    {
        $routesJSHelperPath = $this->input->getOption('path');
        $routesJSHelperPath = Path::join($routesJSHelperPath, 'generated_uri_helper.js');
        if (JsUriHelperGenerator::generate()->saveToFile($routesJSHelperPath) !== false) {
            $this->output->writeln("File with JS uri helpers is generated in <info>$routesJSHelperPath</info>");
        }
    }

    private function generateEs6Helper()
    {
        $config = Config::getValue('app', 'routes', 'helper-es6') ?: [];
        $path = Arrays::getValue($config, 'path') ?? Path::join(ROOT_PATH, 'public');
        $format = Arrays::getValue($config, 'format') ?: 'js';
        $routesEs6HelperPath = Path::join($path, 'generatedUriHelper.' . $format);
        if (Es6UriHelperGenerator::generate($format)->saveToFile($routesEs6HelperPath) !== false) {
            $this->output->writeln("File with ES6 uri helpers is generated in <info>$routesEs6HelperPath</info>");
        }
    }

    private function controller()
    {
        $controller = $this->input->getOption('controller');
        $this->renderRoutes(Route::getRoutesForController($controller));
    }

    private function all()
    {
        $this->renderRoutes(Route::getRoutes());
    }

    private function renderRoutes($routes = [])
    {
        $table = new Table($this->output);
        $table->setHeaders(['URL Helper', 'HTTP Verb', 'Path', 'Controller#Action']);

        foreach ($routes as $route) {
            $method = $this->getRuleMethod($route);
            $action = $route->getAction() ? '#' . $route->getAction() : $route->getAction();
            $controllerAction = $route->getController() . $action;
            $table->addRow([$route->getName(), $method, $route->getUri(), $controllerAction]);
            $this->printExceptIfExists($route, $table);
        }

        $table->render();
    }

    private function getRuleMethod(RouteRule $rule)
    {
        if (!$rule->isRequiredAction()) {
            return 'ALL';
        }
        return is_array($rule->getMethod()) ? 'ANY' : $rule->getMethod();
    }

    private function printExceptIfExists(RouteRule $rule, Table $table)
    {
        $except = $rule->getExcept();
        if ($except) {
            $table->addRow(['', '', '  <info>except:</info>', '']);
            Arrays::map($except, function ($except) use ($table) {
                $table->addRow(['', '', '    ' . $except, '']);
                return $except;
            });
        }
    }
}
