<?php

/**
 * Tests apishka templater tests node expression link test
 *
 * @uses Apishka_Templater_Test_NodeTestCaseAbstract
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Tests_Node_Expression_LinkTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $name = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $args = Apishka_Templater_Node::apishka(
            array(
                'named_arg' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
            )
        );

        $node = Apishka_Templater_Node_Expression_Link::apishka(
            $name,
            $args,
            1
        );

        $this->assertEquals('foo', $node->getNode('name')->getAttribute('value'));
        $this->assertEquals(1, count($node->getNode('args')));
    }

    public function getTests()
    {
        $tests = array();

        // render: no args
        $node = Apishka_Templater_Node_Expression_Link::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(),
            1
        );

        $tests[] = array(
            $node,
            '$this->renderLink("foo", $context)',
        );

        // render: with args
        $node = Apishka_Templater_Node_Expression_Link::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(
                array(
                    'named_arg' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
                )
            ),
            1
        );

        $tests[] = array(
            $node,
            '$this->renderLink("foo", array_replace($context, array("named_arg" => "bar",)))', );

        // display: no args
        $node = Apishka_Templater_Node_Expression_Link::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(),
            1
        );

        $node->setAttribute('output', true);

        $tests[] = array(
            $node,
            '// line 1' . PHP_EOL . '$this->displayLink("foo", $context);',
        );

        // display: with args
        $node = Apishka_Templater_Node_Expression_Link::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
            Apishka_Templater_Node::apishka(
                array(
                    'named_arg' => Apishka_Templater_Node_Expression_Constant::apishka('bar', 1),
                )
            ),
            2
        );

        $node->setAttribute('output', true);

        $tests[] = array(
            $node,
'// line 2
$this->displayLink("foo", array_replace($context, array("named_arg" => "bar",)));', );

        return $tests;
    }
}
