<?php
/**
 * Class Route | Route.php
 * @package Faulancer\View\Helper
 * @author  Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer\View\Helper;

use Faulancer\Exception\RouteInvalidException;
use Faulancer\Service\Config;
use Faulancer\ServiceLocator\ServiceLocator;
use Faulancer\View\AbstractViewHelper;
use Faulancer\View\ViewController;

/**
 * Class Route
 */
class Route extends AbstractViewHelper
{

    /**
     * Get route path by name
     *
     * @param ViewController $view
     * @param string         $name
     * @param array          $parameters
     * @return string
     * @throws RouteInvalidException
     */
    public function __invoke(ViewController $view, string $name, array $parameters = [])
    {
        /** @var Config $config */
        $config = ServiceLocator::instance()->get(Config::class);
        $routes = $config->get('routes');
        $apiRoutes = $config->get('routes:rest');

        $routes = array_merge($routes, $apiRoutes);

        $path   = '';

        foreach ($routes as $routeName => $data) {

            if ($routeName === $name) {
                $path = preg_replace('|/\((.*)\)|', '', $data['path']);
                break;
            }

        }

        if (empty($path)) {
            throw new RouteInvalidException('No route for name "' . $name . '" found');
        }

        if (!empty($parameters)) {

            if (in_array('query', array_keys($parameters), true)) {
                $query = $parameters['query'];
                $query = http_build_query($query);
                $path  = $path . '?' . $query;
            } else {
                $path = $path . '/' . implode('/', $parameters);
            }

        }

        return $path;
    }

}