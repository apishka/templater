<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_TextTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $node = new Apishka_Templater_Node_Text('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('data'));
    }

    public function getTests()
    {
        $tests = array();
        $tests[] = array(new Apishka_Templater_Node_Text('foo', 1), "// line 1\necho \"foo\";");

        return $tests;
    }
}
