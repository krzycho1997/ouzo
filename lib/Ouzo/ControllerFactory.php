<?php
namespace Ouzo;

use Ouzo\Routing\RouteRule;
use Ouzo\Utilities\Strings;

class ControllerFactory
{
    function __construct($controllerPath = "\\Controller\\")
    {
        $globalConfig = Config::getValue('global');
        $this->_defaultAction = $globalConfig['action'];
        $this->controllerPath = $controllerPath;
    }

    public function createController(RouteRule $routeRule)
    {
        $controller = $routeRule->getController();
        $controllerName = Strings::underscoreToCamelCase($controller);
        $controller = $this->controllerPath . $controllerName . "Controller";

        $this->_validateControllerExists($controller);

        return new $controller($routeRule);
    }

    private function _validateControllerExists($controller)
    {
        if (!class_exists($controller)) {
            throw new FrontControllerException('Controller does not exist: ' . $controller);
        }
    }
}