<?php

/*
 * This file is part of Twig.
 *
 * (c) 2013 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Templater_Node_Expression_Binary_EndsWith extends Apishka_Templater_Node_Expression_BinaryAbstract
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames()
    {
        return array(
            'ends with',
        );
    }

    /**
     * Get precedence
     *
     * @return int
     */

    public function getPrecedence()
    {
        return 20;
    }

    /**
     * Get associativity
     *
     * @return int
     */

    public function getAssociativity()
    {
        return Apishka_Templater_ExpressionParser::OPERATOR_LEFT;
    }

    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $left = $compiler->getVarName();
        $right = $compiler->getVarName();
        $compiler
            ->raw(sprintf('(is_string($%s = ', $left))
            ->subcompile($this->getNode('left'))
            ->raw(sprintf(') && is_string($%s = ', $right))
            ->subcompile($this->getNode('right'))
            ->raw(sprintf(') && (\'\' === $%2$s || $%2$s === substr($%1$s, -strlen($%2$s))))', $left, $right))
        ;
    }

    /**
     * Operator
     *
     * @param Apishka_Templater_Compiler $compiler
     * @return Apishka_Templater_Compiler
     */

    public function operator(Apishka_Templater_Compiler $compiler)
    {
        return $compiler->raw('');
    }
}
