<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_SetTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $names = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_AssignName('foo', 1)), array(), 1);
        $values = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_Constant('foo', 1)), array(), 1);
        $node = new Apishka_Templater_Node_Set(false, $names, $values, 1);

        $this->assertEquals($names, $node->getNode('names'));
        $this->assertEquals($values, $node->getNode('values'));
        $this->assertFalse($node->getAttribute('capture'));
    }

    public function getTests()
    {
        $tests = array();

        $names = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_AssignName('foo', 1)), array(), 1);
        $values = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_Constant('foo', 1)), array(), 1);
        $node = new Apishka_Templater_Node_Set(false, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$context["foo"] = "foo";
EOF
        );

        $names = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_AssignName('foo', 1)), array(), 1);
        $values = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Print(new Apishka_Templater_Node_Expression_Constant('foo', 1), 1)), array(), 1);
        $node = new Apishka_Templater_Node_Set(true, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
ob_start();
echo "foo";
\$context["foo"] = ('' === \$tmp = ob_get_clean()) ? '' : new Apishka_Templater_Markup(\$tmp, \$this->env->getCharset());
EOF
        );

        $names = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_AssignName('foo', 1)), array(), 1);
        $values = new Apishka_Templater_Node_Text('foo', 1);
        $node = new Apishka_Templater_Node_Set(true, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$context["foo"] = ('' === \$tmp = "foo") ? '' : new Apishka_Templater_Markup(\$tmp, \$this->env->getCharset());
EOF
        );

        $names = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_AssignName('foo', 1), new Apishka_Templater_Node_Expression_AssignName('bar', 1)), array(), 1);
        $values = new Apishka_Templater_Node(array(new Apishka_Templater_Node_Expression_Constant('foo', 1), new Apishka_Templater_Node_Expression_Name('bar', 1)), array(), 1);
        $node = new Apishka_Templater_Node_Set(false, $names, $values, 1);
        $tests[] = array($node, <<<EOF
// line 1
list(\$context["foo"], \$context["bar"]) = array("foo", {$this->getVariableGetter('bar')});
EOF
        );

        return $tests;
    }
}
