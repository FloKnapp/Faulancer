<?php

namespace Faulancer\View;

use Faulancer\Event\Observer;
use Faulancer\Event\Type\OnPostRender;
use Faulancer\Event\Type\OnRender;
use Faulancer\Exception\ConstantMissingException;
use Faulancer\Exception\FileNotFoundException;
use Faulancer\Exception\ViewHelperException;
use Faulancer\Service\Config;
use Faulancer\ServiceLocator\ServiceLocator;

/**
 * Class ViewController | ViewController.php
 *
 * @package Faulancer\View
 * @author Florian Knapp <office@florianknapp.de>
 */
class ViewController
{

    /**
     * @var Config
     */
    private $config;

    /**
     * Holds the view variables
     * @var array
     */
    private $variable = [];

    /**
     * Holds the view template
     * @var string
     */
    private $template = '';

    /**
     * @var string
     */
    private $templatePath = '';

    /**
     * @var array
     */
    private $viewHelpers = [];

    /**
     * Holds the parent template
     * @var ViewController
     */
    private $parentTemplate = null;

    public function __construct()
    {
        $this->config = ServiceLocator::instance()->get(Config::class);
    }

    /**
     * Set template for this view
     *
     * @param string $template
     * @return ViewController
     * @throws ConstantMissingException
     * @throws FileNotFoundException
     */
    public function setTemplate(string $template = '') :self
    {
        if (empty($this->templatePath) && strpos($template, $this->config->get('viewsRoot')) === false) {
            $template = $this->config->get('viewsRoot') . $template;
        } else {
            $template = $this->templatePath . $template;
        }

        if (empty($template) || !file_exists($template) || is_dir($template)) {
            throw new FileNotFoundException('Template "' . $template . '" not found');
        }

        $this->template = $template;

        return $this;
    }

    /**
     * @param string $path
     * @return ViewController
     */
    public function setTemplatePath(string $path = '') :self
    {
        $this->templatePath = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * Add javascript from outside
     *
     * @param string $file
     * @return self
     */
    public function addScript(string $file) :self
    {
        $this->variable['assetsJs'][] = $file;
        return $this;
    }

    /**
     * Add stylesheet from outside
     *
     * @param string $file
     * @param bool   $minify
     * @return self
     */
    public function addStylesheet(string $file, $minify = false) :self
    {
        $this->variable['assetsCss'][] = $file;
        return $this;
    }

    /**
     * Return current template
     *
     * @return string
     */
    public function getTemplate() :string
    {
        return (string)$this->template;
    }

    /**
     * Set a single variable
     *
     * @param string $key
     * @param string|array $value
     */
    public function setVariable(string $key = '', $value = '')
    {
        $this->variable[$key] = $value;
    }

    /**
     * Get a single variable
     *
     * @param string $key
     * @return string|array
     */
    public function getVariable(string $key)
    {
        if(isset($this->variable[$key])) {
            return $this->variable[$key];
        }

        return '';
    }

    /**
     * Check if variable exists
     *
     * @param string $key
     * @return bool
     */
    public function hasVariable(string $key) :bool
    {
        if(isset($this->variable[$key])) {
            return true;
        }

        return false;
    }

    /**
     * Set many variables at once
     *
     * @param array $variables
     * @return self
     */
    public function setVariables(array $variables = []) :self
    {
        foreach($variables AS $key=>$value) {
            $this->setVariable($key, $value);
        }

        return $this;
    }

    /**
     * Get all variables
     *
     * @return array
     */
    public function getVariables() :array
    {
        return $this->variable;
    }

    /**
     * Define parent template
     *
     * @param ViewController $view
     */
    public function setParentTemplate(ViewController $view)
    {
        $this->parentTemplate = $view;
    }

    /**
     * Get parent template
     *
     * @return ViewController
     */
    public function getParentTemplate()
    {
        return $this->parentTemplate;
    }

    /**
     * Strip spaces and tabs from output
     *
     * @param $output
     * @return string
     */
    private function cleanOutput($output) :string
    {
        if (defined('APPLICATION_ENV') && APPLICATION_ENV === 'production') {
            return preg_replace('/(\s{2,}|\t|\r|\n)/', ' ', trim($output));
        } else {
            return str_replace(["\t", "\r", "\n\n\n"], ' ', trim($output));
        }
    }

    /**
     * Render the current view
     *
     * @return string
     */
    public function render()
    {
        Observer::instance()->trigger(new OnRender($this));

        extract($this->variable);

        ob_start();

        include $this->getTemplate();

        $content = ob_get_contents();

        ob_end_clean();

        Observer::instance()->trigger(new OnPostRender($this));

        if ($this->getParentTemplate() instanceof ViewController) {
            return $this->cleanOutput($this->getParentTemplate()->setVariables($this->getVariables())->render());
        } else {
            return $this->cleanOutput($content);
        }
    }

    /**
     * Magic method for providing a view helper
     *
     * @param  string $name      The class name
     * @param  array  $arguments Arguments if given
     * @return AbstractViewHelper
     * @throws ViewHelperException
     */
    public function __call($name, $arguments)
    {
        $coreViewHelper   = __NAMESPACE__ . '\Helper\\' . ucfirst($name);

        /*
        if (!empty($this->viewHelpers[$coreViewHelper])) {
            return $this->_callUserFuncArray($this->viewHelpers[$coreViewHelper], $arguments);
        } */

        /** @var Config $config */
        $config           = ServiceLocator::instance()->get(Config::class);
        $namespace        = '\\' . $config->get('namespacePrefix');
        $customViewHelper = $namespace . '\\View\\Helper\\' . ucfirst($name);

        /*
        if (!empty($this->viewHelpers[$customViewHelper])) {
            return $this->_callUserFuncArray($this->viewHelpers[$customViewHelper], $arguments);
        } */

        // Search in custom view helpers

        if (class_exists($customViewHelper)) {

            $class = new $customViewHelper;

            $this->viewHelpers[$customViewHelper] = $class;

            return $this->_callUserFuncArray($class, $arguments);

        }

        // No custom view helper found, search in core view helpers

        if (class_exists($coreViewHelper)) {

            $class = new $coreViewHelper;

            $this->viewHelpers[$coreViewHelper] = $class;

            return $this->_callUserFuncArray($class, $arguments);

        }

        throw new ViewHelperException('No view helper for "' . $name . '" found.');
    }

    private function _callUserFuncArray($class, $arguments)
    {
        array_unshift($arguments, $this);
        return call_user_func_array($class, $arguments);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        unset( $this->variable );
        unset( $this->template );
    }

}