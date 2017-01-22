<?php
/**
 * Class Dispatcher
 *
 * @package Faulancer\Controller
 * @author Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer\Controller;

use Faulancer\Exception\ClassNotFoundException;
use Faulancer\Exception\DispatchFailureException;
use Faulancer\Form\AbstractFormHandler;
use Faulancer\Http\Request;
use Faulancer\Http\Response;
use Faulancer\Exception\MethodNotFoundException;
use Faulancer\Service\Config;
use Faulancer\Session\SessionManager;

/**
 * Class Dispatcher
 */
class Dispatcher
{

    /**
     * The current request object
     *
     * @var Request
     */
    protected $request;

    /**
     * The configuration object
     *
     * @var Config
     */
    protected $config;

    /**
     * Dispatcher constructor.
     *
     * @param Request $request
     * @param Config  $config
     */
    public function __construct(Request $request, Config $config)
    {
        $this->request = $request;
        $this->config  = $config;
    }

    /**
     * Bootstrap for every route call
     *
     * @return Response|mixed
     * @throws MethodNotFoundException
     * @throws ClassNotFoundException
     * @throws DispatchFailureException
     */
    public function run()
    {
        if ($formRequest = $this->handleFormRequest()) {
            return $formRequest;
        }

        $response = new Response();

        try {

            $target = $this->getRoute($this->request->getUri());
            $class  = $target['class'];
            $action = $target['action'] . 'Action';

            $class = new $class();

            if (isset($target['var'])) {
                $response->setContent(call_user_func_array([$class, $action], $target['var']));
            } else {
                $response->setContent($class->$action());
            }

        } catch (MethodNotFoundException $e) {

            throw new DispatchFailureException();

        }

        return $response;
    }

    /**
     * Get data for specific route path
     *
     * @param string $path
     *
     * @return array
     * @throws MethodNotFoundException
     */
    private function getRoute($path)
    {
        $routes = require $this->config->get('routeFile');

        foreach ($routes as $name => $data) {

            if ($target = $this->getDirectMatch($path, $data)) {
                return $target;
            } else if ($target = $this->getVariableMatch($path, $data)) {
                return $target;
            }

        }

        throw new MethodNotFoundException('No matching route for path "' . $path . '" found');
    }

    /**
     * Determines if we have a direct/static route match
     *
     * @param string $uri  The request uri
     * @param array  $data The result from ClassParser
     *
     * @return array
     * @throws MethodNotFoundException
     */
    private function getDirectMatch($uri, array $data) :array
    {
        if ($uri === $data['path']) {

            if (strcasecmp($data['method'], $this->request->getMethod()) === 0) {

                return [
                    'class'  => $data['controller'],
                    'action' => $data['action']
                ];

            }

            throw new MethodNotFoundException('Non valid request method available.');

        }

        return [];
    }

    /**
     * Determines if we have a variable route match
     *
     * @param string $uri
     * @param array  $data
     *
     * @return array
     * @throws MethodNotFoundException
     */
    private function getVariableMatch($uri, array $data) :array
    {
        $var = [];

        if ($data['path'] === '/') {
            return [];
        }

        $regex = str_replace(
            ['/', '___'],
            ['\/', '+'],
            $data['path']
        );

        if (preg_match('|^' . $regex . '$|', $uri, $var)) {

            array_splice($var, 0, 1);

            return [
                'class'  => $data['controller'],
                'action' => $data['action'],
                'var'    => $var
            ];

        }

        return [];
    }

    /**
     * Detect a form request
     *
     * @return boolean
     */
    private function handleFormRequest()
    {
        if (strpos($this->request->getUri(), '/formrequest/') !== 0 && $this->request->isPost()) {
            return false;
        }

        $handlerName  = ucfirst(str_replace('/formrequest/', '', $this->request->getUri()));
        $handlerClass = '\\' . $this->config->get('namespacePrefix') . '\\Form\\' . $handlerName . 'Handler';

        if (class_exists($handlerClass)) {

            /** @var AbstractFormHandler $handler */
            $handler = new $handlerClass($this->request, SessionManager::instance());
            return $handler->run();

        }

        return false;
    }

}