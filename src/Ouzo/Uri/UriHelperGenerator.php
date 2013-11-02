<?php
namespace Ouzo\Uri;

use Ouzo\Routing\Route;
use Ouzo\Routing\RouteRule;
use Ouzo\Utilities\Arrays;

class UriHelperGenerator
{
    /**
     * RouteRule[]
     */
    private $_routes;
    private $_generatedFunctions = "<?php\n";

    /**
     * @return UriHelperGenerator
     */
    public static function generate()
    {
        return new self(Route::getRoutes());
    }

    function __construct($routes)
    {
        $this->_routes = $routes;
        $this->_generateFunctions();
    }

    private function _generateFunctions()
    {
        $namesAlreadyGenerated = array();
        foreach ($this->_routes as $route) {
            if (!in_array($route->getName(), $namesAlreadyGenerated)) {
                $this->_generatedFunctions .= $this->_createFunction($route);
            }
            $namesAlreadyGenerated[] = $route->getName();
        }
    }

    private function _createFunction(RouteRule $routeRule)
    {
        $name = $routeRule->getName();
        $uri = $routeRule->getUri();
        $parameters = $this->_prepareParameters($uri);

        $uriWithVariables = str_replace(':', '$', $uri);
        $parametersString = implode(', ', $parameters);

        $ifStatement = $this->_createIf($parameters);

        $function = <<<FUNCTION
function $name($parametersString)
{
    {$ifStatement}return url("$uriWithVariables");
}\n\n
FUNCTION;
        return $name ? $function : '';
    }

    private function _createIf($parameters)
    {
        if ($parameters) {
            $condition = $this->_createIfCondition($parameters);
            $if = <<<IF
if ($condition) {
        throw new \InvalidArgumentException("Missing parameters");
    }\n\t
IF;
            return $if;
        }
        return '';
    }

    private function _createIfCondition($parameters)
    {
        return Arrays::reduce($parameters, function ($result, $element) {
            if ($result == null) {
                $result .= '!isset(' . $element . ') && ';
            } else {
                $result .= ' && !isset(' . $element . ')';
            }
            return rtrim($result, '&& ');
        });
    }

    private function _prepareParameters($uri)
    {
        preg_match_all('#:(\w+)#', $uri, $matches);
        $parameters = Arrays::getValue($matches, 1, array());
        return Arrays::map($parameters, function ($parameter) {
            return '$' . $parameter;
        });
    }

    public function getGeneratedFunctions()
    {
        return trim($this->_generatedFunctions);
    }

    public function saveToFile($file)
    {
        return file_put_contents($file, $this->getGeneratedFunctions());
    }
}
