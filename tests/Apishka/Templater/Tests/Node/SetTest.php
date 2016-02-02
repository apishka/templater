<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_SetTest extends Apishka_Templater_Tests_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $names = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1)), array(), 1);
        $values = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1)), array(), 1);
        $node = Apishka_Templater_Node_Set::apishka(false, $names, $values, 1);

        $this->assertEquals($names, $node->getNode('names'));
        $this->assertEquals($values, $node->getNode('values'));
        $this->assertFalse($node->getAttribute('capture'));
    }

    public function getTests()
    {
        $tests = array();

        $names = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1)), array(), 1);
        $values = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1)), array(), 1);
        $node = Apishka_Templater_Node_Set::apishka(false, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$context["foo"] = "foo";
EOF
        );

        $names = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1)), array(), 1);
        $values = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), 1)), array(), 1);
        $node = Apishka_Templater_Node_Set::apishka(true, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
ob_start();
echo "foo";
\$context["foo"] = ('' === \$tmp = ob_get_clean()) ? '' : new Apishka_Templater_Markup(\$tmp, \$this->env->getCharset());
EOF
        );

        $names = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1)), array(), 1);
        $values = Apishka_Templater_Node_Text::apishka('foo', 1);
        $node = Apishka_Templater_Node_Set::apishka(true, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$context["foo"] = ('' === \$tmp = "foo") ? '' : new Apishka_Templater_Markup(\$tmp, \$this->env->getCharset());
EOF
        );

        $names = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_AssignName::apishka('foo', 1), Apishka_Templater_Node_Expression_AssignName::apishka('bar', 1)), array(), 1);
        $values = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), Apishka_Templater_Node_Expression_Name::apishka('bar', 1)), array(), 1);
        $node = Apishka_Templater_Node_Set::apishka(false, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
list(\$context["foo"], \$context["bar"]) = array("foo", {$this->getVariableGetter('bar')});
EOF
        );

        return $tests;
    }
}
