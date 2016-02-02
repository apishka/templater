<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_Expression_ConditionalTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $expr1 = Apishka_Templater_Node_Expression_Constant::apishka(1, 1);
        $expr2 = Apishka_Templater_Node_Expression_Constant::apishka(2, 1);
        $expr3 = Apishka_Templater_Node_Expression_Constant::apishka(3, 1);
        $node = Apishka_Templater_Node_Expression_Conditional::apishka($expr1, $expr2, $expr3, 1);

        $this->assertEquals($expr1, $node->getNode('expr1'));
        $this->assertEquals($expr2, $node->getNode('expr2'));
        $this->assertEquals($expr3, $node->getNode('expr3'));
    }

    public function getTests()
    {
        $tests = array();

        $expr1 = Apishka_Templater_Node_Expression_Constant::apishka(1, 1);
        $expr2 = Apishka_Templater_Node_Expression_Constant::apishka(2, 1);
        $expr3 = Apishka_Templater_Node_Expression_Constant::apishka(3, 1);
        $node = Apishka_Templater_Node_Expression_Conditional::apishka($expr1, $expr2, $expr3, 1);
        $tests[] = array($node, '((1) ? (2) : (3))');

        return $tests;
    }
}
