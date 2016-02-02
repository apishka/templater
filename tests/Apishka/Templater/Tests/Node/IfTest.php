<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_IfTest extends Apishka_Templater_Tests_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $t = Apishka_Templater_Node::apishka(array(
            Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
            Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1),
        ), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_If::apishka($t, $else, 1);

        $this->assertEquals($t, $node->getNode('tests'));
        $this->assertNull($node->getNode('else'));

        $else = Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('bar', 1), 1);
        $node = Apishka_Templater_Node_If::apishka($t, $else, 1);
        $this->assertEquals($else, $node->getNode('else'));
    }

    public function getTests()
    {
        $tests = array();

        $t = Apishka_Templater_Node::apishka(array(
            Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
            Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1),
        ), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_If::apishka($t, $else, 1);

        $tests[] = array($node, <<<EOF
// line 1
if (true) {
    echo {$this->getVariableGetter('foo')};
}
EOF
        );

        $t = Apishka_Templater_Node::apishka(array(
            Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
            Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1),
            Apishka_Templater_Node_Expression_Constant::apishka(false, 1),
            Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('bar', 1), 1),
        ), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_If::apishka($t, $else, 1);

        $tests[] = array($node, <<<EOF
// line 1
if (true) {
    echo {$this->getVariableGetter('foo')};
} elseif (false) {
    echo {$this->getVariableGetter('bar')};
}
EOF
        );

        $t = Apishka_Templater_Node::apishka(array(
            Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
            Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1),
        ), array(), 1);
        $else = Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('bar', 1), 1);
        $node = Apishka_Templater_Node_If::apishka($t, $else, 1);

        $tests[] = array($node, <<<EOF
// line 1
if (true) {
    echo {$this->getVariableGetter('foo')};
} else {
    echo {$this->getVariableGetter('bar')};
}
EOF
        );

        return $tests;
    }
}
