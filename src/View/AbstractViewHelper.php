<?php

namespace Faulancer\View;

use Faulancer\Exception\ConstantMissingException;
use Faulancer\ServiceLocator\ServiceLocator;

/**
 * Class AbstractViewHelper
 *
 * @method  __invoke()
 * @package Faulancer\View
 * @author  Florian Knapp <office@florianknapp.de>
 */
abstract class AbstractViewHelper
{

    /**
     * @param  string $template
     * @param  array  $variables
     * @return string
     * @throws ConstantMissingException
     */
    protected function renderView(string $template = '', array$variables = [])
    {
        if (!defined('VIEWS_ROOT')) {
            throw new ConstantMissingException('Constant VIEWS_ROOT missing');
        }

        $templatePath = VIEWS_ROOT . '/_helper';
        return (new ViewController())->setTemplate($templatePath . $template)->setVariables($variables)->render();
    }

    /**
     * @return ServiceLocator
     */
    protected function getServiceLocator()
    {
        return ServiceLocator::instance();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->__invoke();
    }

}