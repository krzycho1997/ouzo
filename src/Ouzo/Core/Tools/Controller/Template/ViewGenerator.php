<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Ouzo\Tools\Controller\Template;

use Ouzo\Tools\Utils\ClassPathResolver;
use Ouzo\Utilities\Files;
use Ouzo\Utilities\Path;
use Ouzo\Utilities\Strings;

class ViewGenerator
{
    public function __construct(private string $controller, private ?string $viewPath = null)
    {
    }

    public function getViewName(): string
    {
        $class = Strings::underscoreToCamelCase($this->controller);
        if (Strings::endsWith($class, 'Controller')) {
            return Strings::removeSuffix($class, 'Controller');
        }
        return $class;
    }

    public function createViewDirectoryIfNotExists(): bool
    {
        return $this->preparePaths($this->getViewPath());
    }

    public function getViewPath(): string
    {
        return $this->viewPath ?: ClassPathResolver::forClassAndNamespace($this->getViewName(), $this->getViewNamespace())->getClassDirectory();
    }

    public function getViewNamespace(): string
    {
        return '\\Application\\View';
    }

    private function preparePaths($path): bool
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }
        return false;
    }

    public function appendAction(ActionGenerator $actionGenerator = null): bool
    {
        if ($actionGenerator) {
            if ($this->isActionExists($actionGenerator->getActionViewFile())) {
                return false;
            }
            $actionAppender = new ActionAppender($actionGenerator);
            return $actionAppender->toView($this)->append();
        }
        return false;
    }

    public function isActionExists(string $actionFile): bool
    {
        return Files::exists(Path::join($this->getViewPath(), $actionFile));
    }
}
