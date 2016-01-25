<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_ParentTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_Expression_Parent::apishka('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $tests = array();
        $tests[] = array(Apishka_Templater_Node_Expression_Parent::apishka('foo', 1), '$this->renderParentBlock("foo", $context, $blocks)');

        return $tests;
    }
}
