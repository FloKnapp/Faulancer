<?php

namespace Integration;

use Faulancer\Controller\Dispatcher;
use Faulancer\Http\Request;
use Faulancer\Service\Config;
use Faulancer\ServiceLocator\ServiceLocator;
use PHPUnit\Framework\TestCase;

/**
 * Class FormRequestTest
 * @package Integration
 */
class FormRequestTest extends TestCase
{

    /**
     *
     */
    public function testSuccessFormHandling()
    {
        $_POST = [
            'text/name' => 'Florian',
            'email/email' => 'office@florianknapp.de'
        ];

        $request = new Request();
        $request->setMethod('POST');
        $request->setUri('/formrequest/generic');

        /** @var Config $config */
        $config = ServiceLocator::instance()->get(Config::class);

        $dispatcher = new Dispatcher($request, $config);

        $result = $dispatcher->run();

        $this->assertSame('testSuccess', $result);
    }

    /**
     * 
     */
    public function testInvalidFormHandling()
    {
        $_POST = [
            'text/name' => 'Florian',
            'email/email' => 'office@florianknapp'
        ];

        $request = new Request();
        $request->setMethod('POST');
        $request->setUri('/formrequest/generic');

        /** @var Config $config */
        $config = ServiceLocator::instance()->get(Config::class);

        $dispatcher = new Dispatcher($request, $config);

        $result = $dispatcher->run();

        $this->assertSame('testError', $result);
    }

}