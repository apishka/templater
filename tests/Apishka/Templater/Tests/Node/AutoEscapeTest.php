<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_AutoEscapeTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Text('foo', 1)));
        $node = new Apishka_Templater_Node_AutoEscape(true, $body, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertTrue($node->getAttribute('value'));
    }

    public function getTests()
    {
        $body = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Text('foo', 1)));
        $node = new Apishka_Templater_Node_AutoEscape(true, $body, 1);

        return array(
            array($node, "// line 1\necho \"foo\";"),
        );
    }
}
