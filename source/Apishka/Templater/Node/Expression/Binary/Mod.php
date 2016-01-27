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
class Apishka_Templater_Node_Expression_Binary_Mod extends Apishka_Templater_Node_Expression_BinaryAbstract
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames()
    {
        return array(
            '%',
        );
    }

    /**
     * Get precedence
     *
     * @return int
     */

    public function getPrecedence()
    {
        return 60;
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
     * Operator
     *
     * @param Apishka_Templater_Compiler $compiler
     * @return Apishka_Templater_Compiler
     */

    public function operator(Apishka_Templater_Compiler $compiler)
    {
        return $compiler->raw('%');
    }
}
