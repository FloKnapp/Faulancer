<?php
/**
 * Class Kernel | File Kernel.php
 *
 * @package Faulancer
 * @author Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer;

use Faulancer\Controller\Dispatcher;
use Faulancer\Controller\ErrorController;
use Faulancer\Exception\Exception;
use Faulancer\Http\Request;
use Faulancer\Service\Config;

/**
 * Class Kernel
 */
class Kernel
{

    /**
     * The current request object
     * @var Request
     */
    protected $request;

    /**
     * The configuration object
     * @var Config
     */
    protected $config;

    /**
     * Kernel constructor.
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
     * Initialize the application
     *
     * @return mixed
     * @throws Exception
     * @codeCoverageIgnore
     */
    public function run()
    {
        $dispatcher = new Dispatcher($this->request, $this->config);

        try {

            ob_start();
            echo $dispatcher->dispatch();
            $content = ob_get_contents();
            ob_end_clean();
            return $content;

        } catch (Exception $e) {

            $errorController = new ErrorController($this->request, $e);
            return $errorController->displayError();

        }
    }

}