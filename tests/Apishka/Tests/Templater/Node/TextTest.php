<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_TextTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_Text::apishka('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('data'));
    }

    public function getTests()
    {
        $tests = array();
        $tests[] = array(Apishka_Templater_Node_Text::apishka('foo', 1), "// line 1\necho \"foo\";");

        return $tests;
    }
}
