<?php

/**
 * Apishka templater node translate
 *
 * @uses Apishka_Templater_NodeAbstract
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Translate extends Apishka_Templater_NodeAbstract
{
    /**
     * Construct
     *
     * @param Apishka_Templater_NodeAbstract $body
     * @param array $params
     * @param mixed $variables
     * @param int $lineno
     * @param string $tag
     */

    public function __construct(Apishka_Templater_NodeAbstract $body, array $params, $variables, $lineno, $tag)
    {
        // embedded templates are set as attributes so that they are only visited once by the visitors
        parent::__construct(
            array(
                'body'          => $body,
                'variables'     => $variables,
            ),
            array(
                'params'        => $params,
                'output'        => false
            ),
            $lineno,
            $tag
        );
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        if ($this->getAttribute('output'))
        {
            $compiler
                ->addDebugInfo($this)
                ->write('$this->displayTranslation(')
                ->subcompile($this->getNode('name'))
                ->raw(', ')
            ;
        }
        else
        {
            $compiler
                ->addDebugInfo($this)
                ->write('$this->renderTranslation(')
                ->subcompile($this->getNode('name'))
                ->raw(', ')
            ;
        }

        return;
    }
}
