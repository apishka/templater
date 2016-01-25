<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_TestTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $name = Apishka_Templater_Node_Expression_Constant::apishka('null', 1);
        $args = Apishka_Templater_Node::apishka();
        $node = Apishka_Templater_Node_Expression_Test::apishka($expr, $name, $args, 1);

        $this->assertEquals($expr, $node->getNode('node'));
        $this->assertEquals($args, $node->getNode('arguments'));
        $this->assertEquals($name, $node->getAttribute('name'));
    }

    public function getTests()
    {
        $environment = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'));
        $environment->addTest(new Apishka_Templater_SimpleTest('barbar', 'twig_tests_test_barbar', array('is_variadic' => true, 'need_context' => true)));

        $tests = array();

        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $node = Apishka_Templater_Node_Expression_Test_Null::apishka($expr, 'null', Apishka_Templater_Node::apishka(array()), 1);
        $tests[] = array($node, '(null === "foo")');

        // test as an anonymous function
        $node = $this->createTest(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), 'anonymous', array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1)));
        $tests[] = array($node, 'call_user_func_array($this->env->getTest(\'anonymous\')->getCallable(), array("foo", "foo"))');

        // arbitrary named arguments
        $string = Apishka_Templater_Node_Expression_Constant::apishka('abc', 1);
        $node = $this->createTest($string, 'barbar');
        $tests[] = array($node, 'twig_tests_test_barbar("abc")', $environment);

        $node = $this->createTest($string, 'barbar', array('foo' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_test_barbar("abc", null, null, array("foo" => "bar"))', $environment);

        $node = $this->createTest($string, 'barbar', array('arg2' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_test_barbar("abc", null, "bar")', $environment);

        $node = $this->createTest($string, 'barbar', array(
            Apishka_Templater_Node_Expression_Constant::apishka('1', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('2', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('3', 1),
            'foo' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
        ));
        $tests[] = array($node, 'twig_tests_test_barbar("abc", "1", "2", array(0 => "3", "foo" => "bar"))', $environment);

        return $tests;
    }

    protected function createTest($node, $name, array $arguments = array())
    {
        return Apishka_Templater_Node_Expression_Test::apishka($node, $name, Apishka_Templater_Node::apishka($arguments), 1);
    }

    protected function getEnvironment()
    {
        $env = new Apishka_Templater_Environment(new Apishka_Templater_Loader_Array(array()));
        $env->addTest(new Apishka_Templater_Test('anonymous', function () {}));

        return $env;
    }
}

function twig_tests_test_barbar($string, $arg1 = null, $arg2 = null, array $args = array())
{
}
