<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Templater_Node_Expression_Binary_FloorDiv extends Apishka_Templater_Node_Expression_Binary
{
    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler->raw('intval(floor(');
        parent::compile($compiler);
        $compiler->raw('))');
    }

    public function operator(Apishka_Templater_Compiler $compiler)
    {
        return $compiler->raw('/');
    }
}
