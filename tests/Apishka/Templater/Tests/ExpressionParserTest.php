<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_ExpressionParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @dataProvider getFailingTestsForAssignment
     */
    public function testCanOnlyAssignToNames($template)
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize($template, 'index'));
    }

    public function getFailingTestsForAssignment()
    {
        return array(
            array('{% set false = "foo" %}'),
            array('{% set true = "foo" %}'),
            array('{% set none = "foo" %}'),
            array('{% set 3 = "foo" %}'),
            array('{% set 1 + 2 = "foo" %}'),
            array('{% set "bar" = "foo" %}'),
            array('{% set %}{% endset %}'),
        );
    }

    /**
     * @dataProvider getTestsForArray
     */
    public function testArrayExpression($template, $expected)
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $stream = $env->tokenize($template, 'index');
        $parser = new Apishka_Templater_Parser($env);

        $this->assertEquals($expected, $parser->parse($stream)->getNode('body')->getNode(0)->getNode('expr'));
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @dataProvider getFailingTestsForArray
     */
    public function testArraySyntaxError($template)
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize($template, 'index'));
    }

    public function getFailingTestsForArray()
    {
        return array(
            array('{{ [1, "a": "b"] }}'),
            array('{{ {"a": "b", 2} }}'),
        );
    }

    public function getTestsForArray()
    {
        return array(
            // simple array
            array('{{ [1, 2] }}', Apishka_Templater_Node_Expression_Array::apishka(array(
                  Apishka_Templater_Node_Expression_Constant::apishka(0, 1),
                  Apishka_Templater_Node_Expression_Constant::apishka(1, 1),

                  Apishka_Templater_Node_Expression_Constant::apishka(1, 1),
                  Apishka_Templater_Node_Expression_Constant::apishka(2, 1),
                ), 1),
            ),

            // array with trailing ,
            array('{{ [1, 2, ] }}', Apishka_Templater_Node_Expression_Array::apishka(array(
                  Apishka_Templater_Node_Expression_Constant::apishka(0, 1),
                  Apishka_Templater_Node_Expression_Constant::apishka(1, 1),

                  Apishka_Templater_Node_Expression_Constant::apishka(1, 1),
                  Apishka_Templater_Node_Expression_Constant::apishka(2, 1),
                ), 1),
            ),

            // simple hash
            array('{{ {"a": "b", "b": "c"} }}', Apishka_Templater_Node_Expression_Array::apishka(array(
                  Apishka_Templater_Node_Expression_Constant::apishka('a', 1),
                  Apishka_Templater_Node_Expression_Constant::apishka('b', 1),

                  Apishka_Templater_Node_Expression_Constant::apishka('b', 1),
                  Apishka_Templater_Node_Expression_Constant::apishka('c', 1),
                ), 1),
            ),

            // hash with trailing ,
            array('{{ {"a": "b", "b": "c", } }}', Apishka_Templater_Node_Expression_Array::apishka(array(
                  Apishka_Templater_Node_Expression_Constant::apishka('a', 1),
                  Apishka_Templater_Node_Expression_Constant::apishka('b', 1),

                  Apishka_Templater_Node_Expression_Constant::apishka('b', 1),
                  Apishka_Templater_Node_Expression_Constant::apishka('c', 1),
                ), 1),
            ),

            // hash in an array
            array('{{ [1, {"a": "b", "b": "c"}] }}', Apishka_Templater_Node_Expression_Array::apishka(array(
                  Apishka_Templater_Node_Expression_Constant::apishka(0, 1),
                  Apishka_Templater_Node_Expression_Constant::apishka(1, 1),

                  Apishka_Templater_Node_Expression_Constant::apishka(1, 1),
                  Apishka_Templater_Node_Expression_Array::apishka(array(
                        Apishka_Templater_Node_Expression_Constant::apishka('a', 1),
                        Apishka_Templater_Node_Expression_Constant::apishka('b', 1),

                        Apishka_Templater_Node_Expression_Constant::apishka('b', 1),
                        Apishka_Templater_Node_Expression_Constant::apishka('c', 1),
                      ), 1),
                ), 1),
            ),

            // array in a hash
            array('{{ {"a": [1, 2], "b": "c"} }}', Apishka_Templater_Node_Expression_Array::apishka(array(
                  Apishka_Templater_Node_Expression_Constant::apishka('a', 1),
                  Apishka_Templater_Node_Expression_Array::apishka(array(
                        Apishka_Templater_Node_Expression_Constant::apishka(0, 1),
                        Apishka_Templater_Node_Expression_Constant::apishka(1, 1),

                        Apishka_Templater_Node_Expression_Constant::apishka(1, 1),
                        Apishka_Templater_Node_Expression_Constant::apishka(2, 1),
                      ), 1),
                  Apishka_Templater_Node_Expression_Constant::apishka('b', 1),
                  Apishka_Templater_Node_Expression_Constant::apishka('c', 1),
                ), 1),
            ),
        );
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     */
    public function testStringExpressionDoesNotConcatenateTwoConsecutiveStrings()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $stream = $env->tokenize('{{ "a" "b" }}', 'index');
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($stream);
    }

    /**
     * @dataProvider getTestsForString
     */
    public function testStringExpression($template, $expected)
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $stream = $env->tokenize($template, 'index');
        $parser = new Apishka_Templater_Parser($env);

        $this->assertEquals($expected, $parser->parse($stream)->getNode('body')->getNode(0)->getNode('expr'));
    }

    public function getTestsForString()
    {
        return array(
            array(
                '{{ "foo" }}', Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            ),
            array(
                '{{ "foo #{bar}" }}', Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                    Apishka_Templater_Node_Expression_Constant::apishka('foo ', 1),
                    Apishka_Templater_Node_Expression_Name::apishka('bar', 1),
                    1
                ),
            ),
            array(
                '{{ "foo #{bar} baz" }}', Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                    Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                        Apishka_Templater_Node_Expression_Constant::apishka('foo ', 1),
                        Apishka_Templater_Node_Expression_Name::apishka('bar', 1),
                        1
                    ),
                    Apishka_Templater_Node_Expression_Constant::apishka(' baz', 1),
                    1
                ),
            ),

            array(
                '{{ "foo #{"foo #{bar} baz"} baz" }}', Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                    Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                        Apishka_Templater_Node_Expression_Constant::apishka('foo ', 1),
                        Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                            Apishka_Templater_Node_Expression_Binary_Concat::apishka(
                                Apishka_Templater_Node_Expression_Constant::apishka('foo ', 1),
                                Apishka_Templater_Node_Expression_Name::apishka('bar', 1),
                                1
                            ),
                            Apishka_Templater_Node_Expression_Constant::apishka(' baz', 1),
                            1
                        ),
                        1
                    ),
                    Apishka_Templater_Node_Expression_Constant::apishka(' baz', 1),
                    1
                ),
            ),
        );
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     */
    public function testAttributeCallDoesNotSupportNamedArguments()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ foo.bar(name="Foo") }}', 'index'));
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "cycl" function. Did you mean "cycle" in "index" at line 1?
     */
    public function testUnknownFunction()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ cycl() }}', 'index'));
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "foobar" function in "index" at line 1.
     */
    public function testUnknownFunctionWithoutSuggestions()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ foobar() }}', 'index'));
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "lowe" filter. Did you mean "lower" in "index" at line 1?
     */
    public function testUnknownFilter()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ 1|lowe }}', 'index'));
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "foobar" filter in "index" at line 1.
     */
    public function testUnknownFilterWithoutSuggestions()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ 1|foobar }}', 'index'));
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "nul" test. Did you mean "null" in "index" at line 1
     */
    public function testUnknownTest()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ 1 is nul }}', 'index'));
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "foobar" test in "index" at line 1.
     */
    public function testUnknownTestWithoutSuggestions()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));
        $parser = new Apishka_Templater_Parser($env);

        $parser->parse($env->tokenize('{{ 1 is foobar }}', 'index'));
    }
}
