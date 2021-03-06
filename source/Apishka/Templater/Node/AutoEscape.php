<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents an autoescape node.
 *
 * The value is the escaping strategy (can be html, js, ...)
 *
 * The true value is equivalent to html.
 *
 * If autoescaping is disabled, then the value is false.
 */
class Apishka_Templater_Node_AutoEscape extends Apishka_Templater_NodeAbstract
{
    public function __construct($value, Apishka_Templater_NodeAbstract $body, $lineno, $tag = 'autoescape')
    {
        parent::__construct(array('body' => $body), array('value' => $value), $lineno, $tag);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('body'));
    }
}
