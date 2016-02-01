<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Templater_Node_Expression_Binary_Power extends Apishka_Templater_Node_Expression_BinaryAbstract
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames()
    {
        return array(
            '**',
        );
    }

    /**
     * Get precedence
     *
     * @return int
     */

    public function getPrecedence()
    {
        return 200;
    }

    /**
     * Get associativity
     *
     * @return int
     */

    public function getAssociativity()
    {
        return Apishka_Templater_ExpressionParser::OPERATOR_RIGHT;
    }

    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->raw('pow(')
            ->subcompile($this->getNode('left'))
            ->raw(', ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    /**
     * Operator
     *
     * @param Apishka_Templater_Compiler $compiler
     *
     * @return Apishka_Templater_Compiler
     */

    public function operator(Apishka_Templater_Compiler $compiler)
    {
        return $compiler->raw('**');
    }
}
