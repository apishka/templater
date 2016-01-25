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
 * Apishka templater node expression binary and
 *
 * @uses Apishka_Templater_Node_Expression_BinaryAbstract
 *
 * @author Evgeny Reykh <evgeny@reykh.com>
 */

class Apishka_Templater_Node_Expression_Binary_And extends Apishka_Templater_Node_Expression_BinaryAbstract
{
    /**
     * Operator
     *
     * @param Apishka_Templater_Compiler $compiler
     *
     * @return Apishka_Templater_Compiler
     */

    public function operator(Apishka_Templater_Compiler $compiler)
    {
        return $compiler->raw('&&');
    }
}
