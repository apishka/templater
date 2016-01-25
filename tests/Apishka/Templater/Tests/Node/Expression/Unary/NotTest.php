<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_Unary_NotTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $expr = new Apishka_Templater_Node_Expression_Constant(1, 1);
        $node = new Apishka_Templater_Node_Expression_Unary_Not($expr, 1);

        $this->assertEquals($expr, $node->getNode('node'));
    }

    public function getTests()
    {
        $node = new Apishka_Templater_Node_Expression_Constant(1, 1);
        $node = new Apishka_Templater_Node_Expression_Unary_Not($node, 1);

        return array(
            array($node, '!1'),
        );
    }
}
