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
class Apishka_Templater_Node_Expression_Binary_BitwiseXor extends Apishka_Templater_Node_Expression_BinaryAbstract
{
    public function operator(Apishka_Templater_Compiler $compiler)
    {
        return $compiler->raw('^');
    }
}
