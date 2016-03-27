<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka templater node expression binary abstract
 */

abstract class Apishka_Templater_Node_Expression_BinaryAbstract extends Apishka_Templater_Node_ExpressionAbstract implements Apishka_Templater_Node_Expression_BinaryInterface
{
    /**
     * Get type name
     *
     * @return string
     */

    public function getTypeName()
    {
        return 'common';
    }

    /**
     * Construct
     *
     * @param Apishka_Templater_NodeAbstract $left
     * @param Apishka_Templater_NodeAbstract $right
     * @param mixed                          $lineno
     */

    public function __construct(Apishka_Templater_NodeAbstract $left, Apishka_Templater_NodeAbstract $right, $lineno)
    {
        parent::__construct(array('left' => $left, 'right' => $right), array(), $lineno);
    }

    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('left'))
            ->raw(' ')
        ;

        $this->operator($compiler);

        $compiler
            ->raw(' ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    /**
     * Operator
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    abstract public function operator(Apishka_Templater_Compiler $compiler);
}
