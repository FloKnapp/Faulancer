<?php

namespace tests\unit;

use Faulancer\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * File ResponseTest.php
 *
 * @author Florian Knapp <office@florianknapp.de>
 */
class ResponseTest extends TestCase
{

    /** @var Response */
    protected $response;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testResponseCode()
    {
        $this->response->setCode(200);

        $this->assertTrue(is_int($this->response->getCode()));
    }

    /**
     * @runInSeparateProcess
     */
    public function testResponseContent()
    {
        $this->response->setContent('TestData');
        $this->assertSame('TestData', $this->response->getContent());
    }

    public function testResponseHeader()
    {
        $this->response->setCode(404);

    }

}