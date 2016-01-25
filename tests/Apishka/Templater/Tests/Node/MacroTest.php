<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_MacroTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $body = Apishka_Templater_Node_Text::apishka('foo', 1);
        $arguments = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_Name::apishka('foo', 1)), array(), 1);
        $node = Apishka_Templater_Node_Macro::apishka('foo', $body, $arguments, 1);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($arguments, $node->getNode('arguments'));
        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        $body = Apishka_Templater_Node_Text::apishka('foo', 1);
        $arguments = Apishka_Templater_Node::apishka(array(
            'foo' => Apishka_Templater_Node_Expression_Constant::apishka(null, 1),
            'bar' => Apishka_Templater_Node_Expression_Constant::apishka('Foo', 1),
        ), array(), 1);
        $node = Apishka_Templater_Node_Macro::apishka('foo', $body, $arguments, 1);

        if (PHP_VERSION_ID >= 50600) {
            $declaration = ', ...$__varargs__';
            $varargs = '$__varargs__';
        } else {
            $declaration = '';
            $varargs = 'func_num_args() > 2 ? array_slice(func_get_args(), 2) : array()';
        }

        return array(
            array($node, <<<EOF
// line 1
public function macro_foo(\$__foo__ = null, \$__bar__ = "Foo"$declaration)
{
    \$context = \$this->env->mergeGlobals(array(
        "foo" => \$__foo__,
        "bar" => \$__bar__,
        "varargs" => $varargs,
    ));

    \$blocks = array();

    ob_start();
    try {
        echo "foo";

        return ('' === \$tmp = ob_get_contents()) ? '' : new Apishka_Templater_Markup(\$tmp, \$this->env->getCharset());
    } finally {
        ob_end_clean();
    }
}
EOF
            ),
        );
    }
}
