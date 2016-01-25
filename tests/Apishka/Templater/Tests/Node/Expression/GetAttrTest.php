<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_GetAttrTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $expr = Apishka_Templater_Node_Expression_Name::apishka('foo', 1);
        $attr = Apishka_Templater_Node_Expression_Constant::apishka('bar', 1);
        $args = Apishka_Templater_Node_Expression_Array::apishka(array(), 1);
        $args->addElement(Apishka_Templater_Node_Expression_Name::apishka('foo', 1));
        $args->addElement(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1));
        $node = Apishka_Templater_Node_Expression_GetAttr::apishka($expr, $attr, $args, Apishka_Templater_TemplateAbstract::ARRAY_CALL, 1);

        $this->assertEquals($expr, $node->getNode('node'));
        $this->assertEquals($attr, $node->getNode('attribute'));
        $this->assertEquals($args, $node->getNode('arguments'));
        $this->assertEquals(Apishka_Templater_TemplateAbstract::ARRAY_CALL, $node->getAttribute('type'));
    }

    public function getTests()
    {
        $tests = array();

        $expr = Apishka_Templater_Node_Expression_Name::apishka('foo', 1);
        $attr = Apishka_Templater_Node_Expression_Constant::apishka('bar', 1);
        $args = Apishka_Templater_Node_Expression_Array::apishka(array(), 1);
        $node = Apishka_Templater_Node_Expression_GetAttr::apishka($expr, $attr, $args, Apishka_Templater_TemplateAbstract::ANY_CALL, 1);
        $tests[] = array($node, sprintf('%s%s, "bar", array())', $this->getAttributeGetter(), $this->getVariableGetter('foo', 1)));

        $node = Apishka_Templater_Node_Expression_GetAttr::apishka($expr, $attr, $args, Apishka_Templater_TemplateAbstract::ARRAY_CALL, 1);
        $tests[] = array($node, sprintf('%s%s, "bar", array(), "array")', $this->getAttributeGetter(), $this->getVariableGetter('foo', 1)));

        $args = Apishka_Templater_Node_Expression_Array::apishka(array(), 1);
        $args->addElement(Apishka_Templater_Node_Expression_Name::apishka('foo', 1));
        $args->addElement(Apishka_Templater_Node_Expression_Constant::apishka('bar', 1));
        $node = Apishka_Templater_Node_Expression_GetAttr::apishka($expr, $attr, $args, Apishka_Templater_TemplateAbstract::METHOD_CALL, 1);
        $tests[] = array($node, sprintf('%s%s, "bar", array(0 => %s, 1 => "bar"), "method")', $this->getAttributeGetter(), $this->getVariableGetter('foo', 1), $this->getVariableGetter('foo')));

        return $tests;
    }
}
