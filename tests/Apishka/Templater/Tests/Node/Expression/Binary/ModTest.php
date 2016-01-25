<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_Binary_ModTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $left = Apishka_Templater_Node_Expression_Constant::apishka(1, 1);
        $right = Apishka_Templater_Node_Expression_Constant::apishka(2, 1);
        $node = Apishka_Templater_Node_Expression_Binary_Mod::apishka($left, $right, 1);

        $this->assertEquals($left, $node->getNode('left'));
        $this->assertEquals($right, $node->getNode('right'));
    }

    public function getTests()
    {
        $left = Apishka_Templater_Node_Expression_Constant::apishka(1, 1);
        $right = Apishka_Templater_Node_Expression_Constant::apishka(2, 1);
        $node = Apishka_Templater_Node_Expression_Binary_Mod::apishka($left, $right, 1);

        return array(
            array($node, '(1 % 2)'),
        );
    }
}
