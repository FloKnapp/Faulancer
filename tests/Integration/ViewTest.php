<?php

namespace Faulancer\Test\Integration;

use Faulancer\Exception\ClassNotFoundException;
use Faulancer\Exception\FileNotFoundException;
use Faulancer\Exception\ViewHelperIncompatibleException;
use Faulancer\ServiceLocator\ServiceLocator;
use Faulancer\Session\SessionManager;
use Faulancer\View\AbstractViewHelper;
use Faulancer\View\GenericViewHelper;
use Faulancer\View\ViewController;
use PHPUnit\Framework\TestCase;

/**
 * File ViewTest.php
 *
 * @author Florian Knapp <office@florianknapp.de>
 */
class ViewTest extends TestCase
{

    public function testViewSetTemplate()
    {
        $view = new ViewController();
        $view->setTemplate('/stubView.phtml');
        $this->assertTrue(is_string($view->getTemplate()));
    }

    public function testViewMissingTemplate()
    {
        $this->expectException(FileNotFoundException::class);
        $view = new ViewController();
        $view->setTemplate('NonExistend.phtml');
    }

    public function testViewRender()
    {
        $view = new ViewController();
        $view->setTemplate('/stubView.phtml');
        $this->assertSame('Test', $view->render());
    }

    public function testViewSetVariable()
    {
        $view = new ViewController();
        $view->setVariable('key', 'value');
        $this->assertTrue(is_string($view->getVariable('key')));
        $this->assertSame('value', $view->getVariable('key'));
    }

    public function testViewSetVariables()
    {
        $view = new ViewController();

        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5'
        ];

        $view->setVariables($data);

        foreach ($view->getVariables() as $key => $value) {

            $this->assertTrue(is_string($value));
            $this->assertSame($value, $view->getVariable($key));

        }

    }

    public function testAddJsAssets()
    {
        $view = new ViewController();
        $inst = $view->addScript('script1.js');
        $this->assertInstanceOf(ViewController::class, $inst);
    }

    public function testAddCssAssets()
    {
        $view = new ViewController();
        $inst = $view->addStylesheet('stylesheet1.css');
        $this->assertInstanceOf(ViewController::class, $inst);
    }

    public function testGetAssets()
    {
        $view = new ViewController();

        $css = [
            'stylesheet1.css',
            'stylesheet2.css',
            'stylesheet3.css',
            'stylesheet4.css',
            'stylesheet5.css'
        ];

        $js = [
            'script1.js',
            'script2.js',
            'script3.js',
            'script4.js',
            'script5.js'
        ];

        foreach ($css as $stylesheet) {
            $view->addStylesheet($stylesheet);
        }

        foreach ($js as $javascript) {
            $view->addScript($javascript);
        }

        $resultCss = $view->assetList('css');
        $resultJs = $view->assetList('js');

        foreach ($css as $stylesheet) {
            $this->assertTrue(strpos($resultCss, $stylesheet) !== false);
        }

        foreach ($js as $script) {
            $this->assertTrue(strpos($resultJs, $script) !== false);
        }

    }

    /**
     * @runInSeparateProcess
     */
    public function testViewHelperCsrf()
    {
        $viewHelper = new ViewController();
        $token = $viewHelper->generateCsrfToken();

        $this->assertSame(SessionManager::instance()->getFlashbag('csrf'), $token);
    }

    /**
     * @runInSeparateProcess
     */
    public function testViewHelperSetGetFormError()
    {
        $data = [
            'key1' => [ ['message' => 'value1'] ],
            'key2' => [ ['message' => 'value2'] ],
            'key3' => [ ['message' => 'value3'] ],
            'key4' => [ ['message' => 'value4'] ],
            'key5' => [ ['message' => 'value5'] ],
        ];

        SessionManager::instance()->setFlashbag('errors', $data);

        $view = new ViewController();

        $this->assertEmpty($view->formError('key6')->get());

        foreach ($data as $key => $value) {

            $this->assertTrue($view->formError($key)->has());

            $this->assertSame($value, $data[$key]);
            $this->assertArrayHasKey('message', $value[0]);

            $err = $view->formError($key)->get();

            $this->assertStringStartsWith('<div class="form-error ' . $key . '">', $err);

        }

    }

    /**
     * @runInSeparateProcess
     */
    public function testSetGetFormData()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5',
        ];

        $view = new ViewController();

        SessionManager::instance()->setFlashbagFormData($data);

        foreach ($data as $key => $value) {

            $this->assertSame($value, $view->formData($key));

        }

    }

    /**
     * @runInSeparateProcess
     */
    public function testViewParentTemplate()
    {
        $view = new ViewController();
        $view->setTemplate('/stubBody.phtml');

        $content = $view->render();

        $this->assertInstanceOf(ViewController::class, $view);
        $this->assertSame('LayoutTestContent', $content);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRenderBlockDefaultValue()
    {
        $view = new ViewController();
        $view->setTemplate('/stubBodyEmpty.phtml');

        $content = $view->render();

        $this->assertInstanceOf(ViewController::class, $view);
        $this->assertSame('LayoutdefaultValue', $content);
    }


    public function testCustomViewHelper()
    {
        $view = new ViewController();
        $viewHelper = $view->stubViewHelper();

        $returnValue = (string)$viewHelper;

        $this->assertSame('Test', $returnValue);

    }

    public function testGetMissingViewHelper()
    {
        $this->expectException(ClassNotFoundException::class);
        $view = new ViewController();
        $view->NonExistingViewHelper();
    }

    public function testGetMissingVariable()
    {
        $view = new ViewController();
        $var = $view->getVariable('nonExistend');
        $this->assertEmpty($var);
    }

    public function testHasVariable()
    {
        $view = new ViewController();
        $view->setVariable('testKey', 'testValue');
        $this->assertTrue($view->hasVariable('testKey'));
    }

    public function testHasNotVariable()
    {
        $view = new ViewController();
        $this->assertFalse($view->hasVariable('testKey'));
    }

    public function testGenericViewHelperRender()
    {
        $view = new ViewController();

        $response = $view->renderView('/stubView.phtml');

        $this->assertNotEmpty($response);
        $this->assertTrue(is_string($response));
    }

    public function testGenericViewHelperEscape()
    {
        $view = new ViewController();

        $string = 'Test\\';

        $this->assertTrue(is_string($string));

        $stripped = $view->escapeString($string);

        $this->assertFalse(strpos($stripped, '\\') > 0);

    }

    public function testAbstractViewHelper()
    {
        /** @var AbstractViewHelper $mock */
        $mock = $this->getMockForAbstractClass(AbstractViewHelper::class);
        $this->assertSame(ServiceLocator::instance(), $mock->getServiceLocator());

        $this->expectOutputString($mock);
    }

    /**
     * @runInSeparateProcess
     */
    public function testTranslateHelper()
    {
        $view = new ViewController();
        $this->assertSame('Test-Item1', $view->translate('test_item_1'));
    }

    public function testGetRouteByName()
    {
        $view = new ViewController();
        $this->assertSame('/stub', $view->route('stubStatic'));
    }

    public function testGetNonExistentRouteByName()
    {
        $this->expectException(\Exception::class);
        $view = new ViewController();
        $this->assertNotSame('/stub', $view->route('stubNonExistent'));
    }

    public function testGetRouteNameWithParameters()
    {
        $view = new ViewController();
        $this->assertSame('/stub/test', $view->route('stubStatic', ['test']));
    }

}