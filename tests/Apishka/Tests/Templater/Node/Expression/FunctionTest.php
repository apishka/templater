<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_Expression_FunctionTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $name = 'function';
        $args = Apishka_Templater_Node::apishka();
        $node = Apishka_Templater_Node_Expression_Function::apishka($name, $args, 1);

        $this->assertEquals($name, $node->getAttribute('name'));
        $this->assertEquals($args, $node->getNode('arguments'));
    }

    public function getTests()
    {
        $environment = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'));
        $environment->addFunction(new Apishka_Templater_Function('foo', 'twig_tests_function_dummy', array()));
        $environment->addFunction(new Apishka_Templater_Function('bar', 'twig_tests_function_dummy', array('needs_environment' => true)));
        $environment->addFunction(new Apishka_Templater_Function('foofoo', 'twig_tests_function_dummy', array('needs_context' => true)));
        $environment->addFunction(new Apishka_Templater_Function('foobar', 'twig_tests_function_dummy', array('needs_environment' => true, 'needs_context' => true)));
        $environment->addFunction(new Apishka_Templater_Function('barbar', 'twig_tests_function_barbar', array('is_variadic' => true)));

        $tests = array();

        $node = $this->createFunction('foo');
        $tests[] = array($node, 'twig_tests_function_dummy()', $environment);

        $node = $this->createFunction('foo', array(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1), Apishka_Templater_Node_Expression_Constant::apishka('foobar', 1)));
        $tests[] = array($node, 'twig_tests_function_dummy("bar", "foobar")', $environment);

        $node = $this->createFunction('bar');
        $tests[] = array($node, 'twig_tests_function_dummy($this->env)', $environment);

        $node = $this->createFunction('bar', array(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_function_dummy($this->env, "bar")', $environment);

        $node = $this->createFunction('foofoo');
        $tests[] = array($node, 'twig_tests_function_dummy($context)', $environment);

        $node = $this->createFunction('foofoo', array(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_function_dummy($context, "bar")', $environment);

        $node = $this->createFunction('foobar');
        $tests[] = array($node, 'twig_tests_function_dummy($this->env, $context)', $environment);

        $node = $this->createFunction('foobar', array(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_function_dummy($this->env, $context, "bar")', $environment);

        // named arguments
        $node = $this->createFunction('date', array(
            'timezone' => Apishka_Templater_Node_Expression_Constant::apishka('America/Chicago', 1),
            'date'     => Apishka_Templater_Node_Expression_Constant::apishka(0, 1),
        ));
        $tests[] = array($node, 'twig_date_converter($this->env, 0, "America/Chicago")');

        // arbitrary named arguments
        $node = $this->createFunction('barbar');
        $tests[] = array($node, 'twig_tests_function_barbar()', $environment);

        $node = $this->createFunction('barbar', array('foo' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_function_barbar(null, null, array("foo" => "bar"))', $environment);

        $node = $this->createFunction('barbar', array('arg2' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_function_barbar(null, "bar")', $environment);

        $node = $this->createFunction('barbar', array(
            Apishka_Templater_Node_Expression_Constant::apishka('1', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('2', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('3', 1),
            'foo' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
        ));
        $tests[] = array($node, 'twig_tests_function_barbar("1", "2", array(0 => "3", "foo" => "bar"))', $environment);

        // function as an anonymous function
        $node = $this->createFunction('anonymous', array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1)));
        $tests[] = array($node, 'call_user_func_array($this->env->getFunction(\'anonymous\')->getCallable(), array("foo"))');

        return $tests;
    }

    protected function createFunction($name, array $arguments = array())
    {
        return Apishka_Templater_Node_Expression_Function::apishka($name, Apishka_Templater_Node::apishka($arguments), 1);
    }

    protected function getEnvironment()
    {
        $env = new Apishka_Templater_Environment(new Apishka_Templater_Loader_Array(array()));
        $env->addFunction(new Apishka_Templater_Function('anonymous', function () {}));

        return $env;
    }
}

function twig_tests_function_dummy()
{
}

function twig_tests_function_barbar($arg1 = null, $arg2 = null, array $args = array())
{
}
