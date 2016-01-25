<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_FilterTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $name = Apishka_Templater_Node_Expression_Constant::apishka('upper', 1);
        $args = Apishka_Templater_Node::apishka();
        $node = Apishka_Templater_Node_Expression_Filter::apishka($expr, $name, $args, 1);

        $this->assertEquals($expr, $node->getNode('node'));
        $this->assertEquals($name, $node->getNode('filter'));
        $this->assertEquals($args, $node->getNode('arguments'));
    }

    public function getTests()
    {
        $environment = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'));
        $environment->addFilter(new Apishka_Templater_SimpleFilter('bar', 'twig_tests_filter_dummy', array('needs_environment' => true)));
        $environment->addFilter(new Apishka_Templater_SimpleFilter('barbar', 'twig_tests_filter_barbar', array('needs_context' => true, 'is_variadic' => true)));

        $tests = array();

        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $node = $this->createFilter($expr, 'upper');
        $node = $this->createFilter($node, 'number_format', array(Apishka_Templater_Node_Expression_Constant::apishka(2, 1), Apishka_Templater_Node_Expression_Constant::apishka('.', 1), Apishka_Templater_Node_Expression_Constant::apishka(',', 1)));

        $tests[] = array($node, 'twig_number_format_filter($this->env, twig_upper_filter($this->env, "foo"), 2, ".", ",")');

        // named arguments
        $date = Apishka_Templater_Node_Expression_Constant::apishka(0, 1);
        $node = $this->createFilter($date, 'date', array(
            'timezone' => Apishka_Templater_Node_Expression_Constant::apishka('America/Chicago', 1),
            'format'   => Apishka_Templater_Node_Expression_Constant::apishka('d/m/Y H:i:s P', 1),
        ));
        $tests[] = array($node, 'twig_date_format_filter($this->env, 0, "d/m/Y H:i:s P", "America/Chicago")');

        // skip an optional argument
        $date = Apishka_Templater_Node_Expression_Constant::apishka(0, 1);
        $node = $this->createFilter($date, 'date', array(
            'timezone' => Apishka_Templater_Node_Expression_Constant::apishka('America/Chicago', 1),
        ));
        $tests[] = array($node, 'twig_date_format_filter($this->env, 0, null, "America/Chicago")');

        // underscores vs camelCase for named arguments
        $string = Apishka_Templater_Node_Expression_Constant::apishka('abc', 1);
        $node = $this->createFilter($string, 'reverse', array(
            'preserve_keys' => Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
        ));
        $tests[] = array($node, 'twig_reverse_filter($this->env, "abc", true)');
        $node = $this->createFilter($string, 'reverse', array(
            'preserveKeys' => Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
        ));
        $tests[] = array($node, 'twig_reverse_filter($this->env, "abc", true)');

        // filter as an anonymous function
        $node = $this->createFilter(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), 'anonymous');
        $tests[] = array($node, 'call_user_func_array($this->env->getFilter(\'anonymous\')->getCallable(), array("foo"))');

        // needs environment
        $node = $this->createFilter($string, 'bar');
        $tests[] = array($node, 'twig_tests_filter_dummy($this->env, "abc")', $environment);

        $node = $this->createFilter($string, 'bar', array(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_filter_dummy($this->env, "abc", "bar")', $environment);

        // arbitrary named arguments
        $node = $this->createFilter($string, 'barbar');
        $tests[] = array($node, 'twig_tests_filter_barbar($context, "abc")', $environment);

        $node = $this->createFilter($string, 'barbar', array('foo' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_filter_barbar($context, "abc", null, null, array("foo" => "bar"))', $environment);

        $node = $this->createFilter($string, 'barbar', array('arg2' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1)));
        $tests[] = array($node, 'twig_tests_filter_barbar($context, "abc", null, "bar")', $environment);

        $node = $this->createFilter($string, 'barbar', array(
            Apishka_Templater_Node_Expression_Constant::apishka('1', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('2', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('3', 1),
            'foo' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
        ));
        $tests[] = array($node, 'twig_tests_filter_barbar($context, "abc", "1", "2", array(0 => "3", "foo" => "bar"))', $environment);

        return $tests;
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown argument "foobar" for filter "date(format, timezone)" at line 1.
     */
    public function testCompileWithWrongNamedArgumentName()
    {
        $date = Apishka_Templater_Node_Expression_Constant::apishka(0, 1);
        $node = $this->createFilter($date, 'date', array(
            'foobar' => Apishka_Templater_Node_Expression_Constant::apishka('America/Chicago', 1),
        ));

        $compiler = $this->getCompiler();
        $compiler->compile($node);
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Value for argument "from" is required for filter "replace".
     */
    public function testCompileWithMissingNamedArgument()
    {
        $value = Apishka_Templater_Node_Expression_Constant::apishka(0, 1);
        $node = $this->createFilter($value, 'replace', array(
            'to' => Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
        ));

        $compiler = $this->getCompiler();
        $compiler->compile($node);
    }

    protected function createFilter($node, $name, array $arguments = array())
    {
        $name = Apishka_Templater_Node_Expression_Constant::apishka($name, 1);
        $arguments = Apishka_Templater_Node::apishka($arguments);

        return Apishka_Templater_Node_Expression_Filter::apishka($node, $name, $arguments, 1);
    }

    protected function getEnvironment()
    {
        $env = new Apishka_Templater_Environment(new Apishka_Templater_Loader_Array(array()));
        $env->addFilter(new Apishka_Templater_Filter('anonymous', function () {}));

        return $env;
    }
}

function twig_tests_filter_dummy()
{
}

function twig_tests_filter_barbar($context, $string, $arg1 = null, $arg2 = null, array $args = array())
{
}
