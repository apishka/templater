<?php

/**
 * Apishka templater node expression test empty
 *
 * @easy-extend-base
 *
 * @uses Apishka_Templater_Node_Expression_Test
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Expression_Test_Empty extends Apishka_Templater_Node_Expression_Test
{
    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $test = $compiler->getEnvironment()->getTest($name);

        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'test');
        $this->setAttribute('callable', 'twig_test_empty');
        $this->setAttribute('is_variadic', $test->isVariadic());

        $this->compileCallable($compiler);
    }
}
