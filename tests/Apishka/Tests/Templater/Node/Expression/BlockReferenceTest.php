<?php

/**
 * Apishka templater tests node expression block reference test
 *
 * @uses Apishka_Tests_Templater_Test_NodeTestCaseAbstract
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Tests_Templater_Node_Expression_BlockReferenceTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $name = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $args = Apishka_Templater_Node::apishka(
            array(
                'named_arg' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
            )
        );

        $node = Apishka_Templater_Node_Expression_BlockReference::apishka(
            $name,
            $args,
            false,
            1
        );

        $this->assertEquals('foo', $node->getNode('name')->getAttribute('value'));
        $this->assertEquals(1, count($node->getNode('args')));
    }

    public function getTests()
    {
        $tests = array();

        // render: no args
        $node = Apishka_Templater_Node_Expression_BlockReference::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(),
            false,
            1
        );

        $tests[] = array(
            $node,
            '$this->renderBlock("foo", $context, $blocks)',
        );

        // render: with args
        $node = Apishka_Templater_Node_Expression_BlockReference::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(
                array(
                    'named_arg' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
                )
            ),
            false,
            1
        );

        $tests[] = array(
            $node,
            '$this->renderBlock("foo", array_replace($context, array("named_arg" => "bar",)), $blocks)', );

        // display: no args
        $node = Apishka_Templater_Node_Expression_BlockReference::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(),
            false,
            1
        );

        $node->setAttribute('output', true);

        $tests[] = array(
            $node,
            '// line 1' . PHP_EOL . '$this->displayBlock("foo", $context, $blocks);',
        );

        // display: with args
        $node = Apishka_Templater_Node_Expression_BlockReference::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(
                array(
                    'named_arg' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
                )
            ),
            false,
            1
        );

        $node->setAttribute('output', true);

        $tests[] = array(
            $node,
'// line 1
$this->displayBlock("foo", array_replace($context, array("named_arg" => "bar",)), $blocks);', );

        return $tests;
    }
}
