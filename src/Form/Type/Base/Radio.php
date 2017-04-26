<?php
/**
 * Class Radio | Radio.php
 * @package Faulancer\Form\Type\Base
 * @author Florian Knapp <office@florianknapp.de>
 */
namespace Faulancer\Form\Type\Base;

use Faulancer\Form\Type\AbstractType;

/**
 * Class Radio
 */
class Radio extends AbstractType
{

    protected $element = [];

    protected $inputType = 'input';

    public function create()
    {
        $this->setLabel($this->definition['label']);

        $result = [];

        foreach ($this->definition['options'] as $optionName => $value) {

            $output = '<' . $this->inputType;

            $output .= ' value="' . $value . '"';

            foreach ($this->definition['attributes'] as $attr => $val) {
                $output .= ' ' . $attr . '="' . $val . '" ';
            }

            if (!empty($this->getValue()) && $value === $this->getValue()) {
                $output .= ' checked="checked"';
            } elseif (empty($this->getValue()) && $this->definition['selected'] === $optionName) {
                $output .= ' checked="checked"';
            }

            $output .= '/>';

            $result[$optionName] = $output;

        }

        $this->element = $result;

        return $this;

    }

    /**
     * @param string $optionName
     * @return string
     */
    public function getOption(string $optionName)
    {
        return $this->element[$optionName];
    }

}