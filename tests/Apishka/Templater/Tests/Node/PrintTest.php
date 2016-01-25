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
        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1);
        $node = Apishka_Templater_Node_Print::apishka($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = array();
        $tests[] = array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), 1), "// line 1\necho \"foo\";");

        return $tests;
    }
}
