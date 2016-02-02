<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_ArrayTest extends Apishka_Templater_Tests_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $elements = array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), $foo = Apishka_Templater_Node_Expression_Constant::apishka('bar', 1));
        $node = Apishka_Templater_Node_Expression_Array::apishka($elements, 1);

        $this->assertEquals($foo, $node->getNode(1));
    }

    public function getTests()
    {
        $elements = array(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),

            Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
        );
        $node = Apishka_Templater_Node_Expression_Array::apishka($elements, 1);

        return array(
            array($node, 'array("foo" => "bar", "bar" => "foo")'),
        );
    }
}
