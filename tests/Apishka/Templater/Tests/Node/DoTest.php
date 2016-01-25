<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_DoTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $node = Apishka_Templater_Node_Do::apishka($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = array();

        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $node = Apishka_Templater_Node_Do::apishka($expr, 1);
        $tests[] = array($node, "// line 1\n\"foo\";");

        return $tests;
    }
}
