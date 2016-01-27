<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka templater node expression test
 *
 * @easy-extend-base
 *
 * @uses Apishka_Templater_Node_Expression_CallAbstract
 * @uses Apishka_Templater_Node_Expression_TestInterface
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

abstract class Apishka_Templater_Node_Expression_TestAbstract extends Apishka_Templater_Node_Expression_CallAbstract implements Apishka_Templater_Node_Expression_TestInterface
{
    public function __construct(Apishka_Templater_NodeAbstract $node, $name, Apishka_Templater_NodeAbstract $arguments = null, $lineno)
    {
        parent::__construct(array('node' => $node, 'arguments' => $arguments), array('name' => $name), $lineno);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $test = $compiler->getEnvironment()->getTest($name);

        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'test');
        $this->setAttribute('callable', $test->getCallable());
        $this->setAttribute('is_variadic', $test->isVariadic());

        $this->compileCallable($compiler);
    }
}
