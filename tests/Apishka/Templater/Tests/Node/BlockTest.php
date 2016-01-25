<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_BlockTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Apishka_Templater_Node_Text('foo', 1);
        $node = new Apishka_Templater_Node_Block('foo', $body, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $body = new Apishka_Templater_Node_Text('foo', 1);
        $node = new Apishka_Templater_Node_Block('foo', $body, 1);

        return array(
            array($node, <<<EOF
// line 1
public function block_foo(\$context, array \$blocks = array())
{
    echo "foo";
}
EOF
            ),
        );
    }
}
