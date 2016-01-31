<?php

/**
 * Apishka templater node translate
 *
 * @uses Apishka_Templater_NodeAbstract
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Translate extends Apishka_Templater_NodeAbstract
{
    public function __construct(Apishka_Templater_NodeAbstract $body, array $params, $variables, $lineno, $tag)
    {
        // embedded templates are set as attributes so that they are only visited once by the visitors
        parent::__construct(
            array(
                'body'              => $body,
                'variables'         => $variables,
            ),
            array(
                'params'            => $params,
            ),
            $lineno,
            $tag
        );
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        return;
    }
}
