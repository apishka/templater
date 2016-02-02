<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_Expression_AssignNameTest extends Apishka_Templater_Tests_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $node = Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1);

        return array(
            array($node, '$context["foo"]'),
        );
    }
}
