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
 * Apishka templater node expression unary abstract
 *
 * @uses Apishka_Templater_Node_ExpressionAbstract
 * @uses Apishka_Templater_Node_Expression_UnaryInterface
 * @abstract
 *
 * @author Evgeny Reykh <evgeny@reykh.com>
 */

abstract class Apishka_Templater_Node_Expression_UnaryAbstract extends Apishka_Templater_Node_ExpressionAbstract implements Apishka_Templater_Node_Expression_UnaryInterface
{
    /**
     * Construct
     *
     * @param Apishka_Templater_NodeAbstract $node
     * @param mixed                          $lineno
     */

    public function __construct(Apishka_Templater_NodeAbstract $node, $lineno)
    {
        parent::__construct(
            array('node' => $node),
            array(),
            $lineno
        );
    }

    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler->raw(' ');
        $this->operator($compiler);
        $compiler->subcompile($this->getNode('node'));
    }

    /**
     * Operator
     *
     * @param Apishka_Templater_Compiler $compiler
     * @abstract
     */

    abstract public function operator(Apishka_Templater_Compiler $compiler);
}
