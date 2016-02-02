<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_Expression_ConstantTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('value'));
    }

    public function getTests()
    {
        $tests = array();

        $node = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $tests[] = array($node, '"foo"');

        return $tests;
    }
}
