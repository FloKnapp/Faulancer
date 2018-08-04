<?php

namespace Faulancer\Test\Integration;

use Faulancer\Exception\ClassNotFoundException;
use Faulancer\Exception\FileNotFoundException;
use Faulancer\Exception\ViewHelperException;
use Faulancer\Fixture\Entity\RoleAuthorEntity;
use Faulancer\Fixture\Entity\UserEntity;
use Faulancer\ORM\User\Entity;
use Faulancer\Service\AuthenticatorService;
use Faulancer\Service\SessionManagerService;
use Faulancer\ServiceLocator\ServiceLocator;
use Faulancer\View\AbstractViewHelper;
use Faulancer\View\ViewController;
use PHPUnit\Framework\TestCase;

/**
 * File ViewTest.php
 *
 * @author Florian Knapp <office@florianknapp.de>
 */
class ViewTest extends TestCase
{

    /** @var SessionManagerService */
    protected $sessionManager;

    public function setUp()
    {
        $this->sessionManager = ServiceLocator::instance()->get(SessionManagerService::class);
    }

    public function testViewSetTemplate()
    {
        $view = new ViewController();
        $view->setTemplate('/stubView.phtml');
        $this->assertTrue(is_string($view->getTemplate()));
    }

    public function testViewSetTemplatePath()
    {
        $view = new ViewController();
        $view->setTemplatePath('/path/to/templates');
        $this->assertSame('/path/to/templates', $view->getTemplatePath());
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

    public function testEmptyAssets()
    {
        $view = new ViewController();
        $resultCss = $view->assetList('css');

        $this->assertSame('', $resultCss);
    }

    public function testAssetOutput()
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

        $resultCss = $view->assetList('css', true);
        $resultJs = $view->assetList('js', true);

        self::assertNotEmpty($resultCss);
        self::assertNotEmpty($resultJs);
    }

    /**
     * @throws FileNotFoundException
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
     * @throws FileNotFoundException
     */
    public function testViewParentTemplateWithInvalidPath()
    {
        $this->expectException(FileNotFoundException::class);
        $view = new ViewController();
        $view->setTemplatePath('/path/to/nowhere');
        $view->parentTemplate('/layout.phtml');
        $view->render();
    }

    /**
     * @throws FileNotFoundException
     */
    public function testRenderBlockDefaultValue()
    {
        $view = new ViewController();
        $view->setTemplate('/stubBodyEmpty.phtml');

        $content = $view->render();

        $this->assertInstanceOf(ViewController::class, $view);
        $this->assertSame('LayoutdefaultValue', $content);
    }

    public function testUsingBlockTwice()
    {
        $view = new ViewController();
        $view->setTemplate('/stubBodySameBlock.phtml');

        $this->assertSame('LayoutTestContent', $view->render());
    }

    public function testEncodeHelper()
    {
        $view = new ViewController();
        $script = '<script>alert("Test");</script>';
        $result = $view->escape($script);

        $this->assertSame('&lt;script&gt;alert(&quot;Test&quot;);&lt;/script&gt;', $result);
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
        $this->expectException(ViewHelperException::class);
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

    public function testGenericViewHelperRenderWithInvalidPath()
    {
        $this->expectException(FileNotFoundException::class);

        $view = new ViewController();
        $view->setTemplatePath('/path/to/nowhere');
        $view->renderView('/stubView.phtml');
    }

    public function testGenericViewHelperEscape()
    {
        $view = new ViewController();

        $string = 'Test\\';

        $this->assertTrue(is_string($string));

        $stripped = $view->escapeString($string);

        $this->assertFalse(strpos($stripped, '\\') > 0);

    }

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

    public function testGetRouteNameWithQueryParameters()
    {
        $view = new ViewController();
        $this->assertSame('/stub?test=test', $view->route('stubStatic', ['query' => ['test' => 'test']]));
    }

    public function testFlashBagViewHelper()
    {
        $view = new ViewController();
        $this->sessionManager->setFlashMessage('test', 'test');
        $this->assertSame('<span class="flash-message default">test</span>', $view->flashMessage('test'));
    }

    public function testUserViewHelper()
    {
        $view = new ViewController();

        $this->sessionManager->set('user', 1);

        $user = new UserEntity();
        $user->roles[] = new RoleAuthorEntity();

        /** @var AuthenticatorService|\PHPUnit_Framework_MockObject_MockObject $authMock */
        $authMock = $this->createPartialMock(AuthenticatorService::class, ['getUserFromSession', 'isPermitted']);
        $authMock->method('getUserFromSession')->willReturn($user);
        $authMock->method('isPermitted')->with(['author'])->willReturn(true);

        ServiceLocator::instance()->set(AuthenticatorService::class, $authMock);
        $this->assertTrue($view->user()->isPermitted(['author']));

        ServiceLocator::instance()->set(AuthenticatorService::class, $authMock);
        $userEntity = $view->user()->get();

        $this->assertTrue($view->user()->isLoggedIn());
        $this->assertNotEmpty($userEntity);
        $this->assertInstanceOf(Entity::class, $userEntity);
    }

}