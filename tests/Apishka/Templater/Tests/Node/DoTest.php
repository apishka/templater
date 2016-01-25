<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_DoTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $expr = new Apishka_Templater_Node_Expression_Constant('foo', 1);
        $node = new Apishka_Templater_Node_Do($expr, 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = array();

        $expr = new Apishka_Templater_Node_Expression_Constant('foo', 1);
        $node = new Apishka_Templater_Node_Do($expr, 1);
        $tests[] = array($node, "// line 1\n\"foo\";");

        return $tests;
    }
}
