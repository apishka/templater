<?php

/*
 * This file is part of Twig.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a do node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Do extends Apishka_Templater_Node
{
    public function __construct(Apishka_Templater_Node_Expression $expr, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
        ;
    }
}
