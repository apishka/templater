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
 * Returns the value or the default value when it is undefined or empty.
 *
 * @easy-extend-base
 *
 * <pre>
 *  {{ var.foo|default('foo item on var is not defined') }}
 * </pre>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Expression_Filter_Default extends Apishka_Templater_Node_Expression_Filter
{
    public function __construct(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Node_Expression_Constant $filterName, Apishka_Templater_NodeAbstract $arguments, $lineno, $tag = null)
    {
        $default = Apishka_Templater_Node_Expression_Filter::apishka($node, Apishka_Templater_Node_Expression_Constant::apishka('default', $node->getLine()), $arguments, $node->getLine());

        if ('default' === $filterName->getAttribute('value') && ($node instanceof Apishka_Templater_Node_Expression_Name || $node instanceof Apishka_Templater_Node_Expression_GetAttr)) {
            $test = Apishka_Templater_Node_Expression_Test_Defined::apishka(clone $node, 'defined', Apishka_Templater_Node::apishka(), $node->getLine());
            $false = count($arguments) ? $arguments->getNode(0) : Apishka_Templater_Node_Expression_Constant::apishka('', $node->getLine());

            $node = Apishka_Templater_Node_Expression_Conditional::apishka($test, $default, $false, $node->getLine());
        } else {
            $node = $default;
        }

        parent::__construct($node, $filterName, $arguments, $lineno, $tag);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
