<?php
/**
 * Class Text
 * @package Faulancer\Form\Type
 */
namespace Faulancer\Form\Type;

/**
 * Class Text
 */
class Text extends AbstractType
{

    /** @var array */
    protected $definition = [];

    /** @var string */
    protected $type = 'input';

    /** @var string */
    protected $element = '';

    /**
     * @return string
     */
    public function create()
    {
        $this->inputLabel = $this->definition['label'];

        $output = '<' . $this->type;

        foreach ($this->definition['attributes'] as $attr => $value) {
            $output .= ' ' . $attr . '="' . $value . '" ';
        }

        $output .= '/>';

        $this->element = $output;

        return $this;
    }

}