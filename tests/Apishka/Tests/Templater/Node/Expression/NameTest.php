<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_Expression_NameTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_Expression_Name::apishka('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $node = Apishka_Templater_Node_Expression_Name::apishka('foo', 1);
        $self = Apishka_Templater_Node_Expression_Name::apishka('_self', 1);
        $context = Apishka_Templater_Node_Expression_Name::apishka('_context', 1);

        $env = new Apishka_Templater_Environment($this->createMock('Apishka_Templater_LoaderInterface'));

        return array(
            array($node, "// line 1\n" . '(isset($context["foo"]) ? $context["foo"] : null)', $env),
            array($node, $this->getVariableGetter('foo', 1), $env),
            array($self, "// line 1\n\$this->getTemplateName()"),
            array($context, "// line 1\n\$context"),
        );
    }
}
