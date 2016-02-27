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
class Apishka_Templater_Node_Expression_Constant extends Apishka_Templater_Node_ExpressionAbstract
{
    public function __construct($value, $lineno)
    {
        parent::__construct(array(), array('value' => $value), $lineno);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler->repr($this->getAttribute('value'));
    }

    /**
     * Is safe all
     *
     * @return bool
     */

    public function isSafeAll()
    {
        return true;
    }
}
