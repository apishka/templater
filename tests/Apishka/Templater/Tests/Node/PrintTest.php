<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_PrintTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $expr = new Apishka_Templater_Node_Expression_Constant('foo', 1);
        $node = new Apishka_Templater_Node_Print($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = array();
        $tests[] = array(new Apishka_Templater_Node_Print(new Apishka_Templater_Node_Expression_Constant('foo', 1), 1), "// line 1\necho \"foo\";");

        return $tests;
    }
}
