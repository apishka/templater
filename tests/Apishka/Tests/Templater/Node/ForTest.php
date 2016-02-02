<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_ForTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $keyTarget = Apishka_Templater_Node_Expression_AssignName::apishka('key', 1);
        $valueTarget = Apishka_Templater_Node_Expression_AssignName::apishka('item', 1);
        $seq = Apishka_Templater_Node_Expression_Name::apishka('items', 1);
        $ifexpr = Apishka_Templater_Node_Expression_Constant::apishka(true, 1);
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1)), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_For::apishka($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, 1);
        $node->setAttribute('with_loop', false);

        $this->assertEquals($keyTarget, $node->getNode('key_target'));
        $this->assertEquals($valueTarget, $node->getNode('value_target'));
        $this->assertEquals($seq, $node->getNode('seq'));
        $this->assertTrue($node->getAttribute('ifexpr'));
        $this->assertEquals('Apishka_Templater_Node_If', get_class($node->getNode('body')));
        $this->assertEquals($body, $node->getNode('body')->getNode('tests')->getNode(1)->getNode(0));
        $this->assertNull($node->getNode('else'));

        $else = Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1);
        $node = Apishka_Templater_Node_For::apishka($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, 1);
        $node->setAttribute('with_loop', false);
        $this->assertEquals($else, $node->getNode('else'));
    }

    public function getTests()
    {
        $tests = array();

        $keyTarget = Apishka_Templater_Node_Expression_AssignName::apishka('key', 1);
        $valueTarget = Apishka_Templater_Node_Expression_AssignName::apishka('item', 1);
        $seq = Apishka_Templater_Node_Expression_Name::apishka('items', 1);
        $ifexpr = null;
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1)), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_For::apishka($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, 1);
        $node->setAttribute('with_loop', false);

        $tests[] = array($node, <<<EOF
// line 1
\$context['_parent'] = \$context;
\$context['_seq'] = twig_ensure_traversable({$this->getVariableGetter('items')});
foreach (\$context['_seq'] as \$context["key"] => \$context["item"]) {
    echo {$this->getVariableGetter('foo')};
}
\$_parent = \$context['_parent'];
unset(\$context['_seq'], \$context['_iterated'], \$context['key'], \$context['item'], \$context['_parent'], \$context['loop']);
\$context = array_intersect_key(\$context, \$_parent) + \$_parent;
EOF
        );

        $keyTarget = Apishka_Templater_Node_Expression_AssignName::apishka('k', 1);
        $valueTarget = Apishka_Templater_Node_Expression_AssignName::apishka('v', 1);
        $seq = Apishka_Templater_Node_Expression_Name::apishka('values', 1);
        $ifexpr = null;
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1)), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_For::apishka($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, 1);
        $node->setAttribute('with_loop', true);

        $tests[] = array($node, <<<EOF
// line 1
\$context['_parent'] = \$context;
\$context['_seq'] = twig_ensure_traversable({$this->getVariableGetter('values')});
\$context['loop'] = array(
  'parent' => \$context['_parent'],
  'index0' => 0,
  'index'  => 1,
  'first'  => true,
);
if (is_array(\$context['_seq']) || (is_object(\$context['_seq']) && \$context['_seq'] instanceof Countable)) {
    \$length = count(\$context['_seq']);
    \$context['loop']['revindex0'] = \$length - 1;
    \$context['loop']['revindex'] = \$length;
    \$context['loop']['length'] = \$length;
    \$context['loop']['last'] = 1 === \$length;
}
foreach (\$context['_seq'] as \$context["k"] => \$context["v"]) {
    echo {$this->getVariableGetter('foo')};
    ++\$context['loop']['index0'];
    ++\$context['loop']['index'];
    \$context['loop']['first'] = false;
    if (isset(\$context['loop']['length'])) {
        --\$context['loop']['revindex0'];
        --\$context['loop']['revindex'];
        \$context['loop']['last'] = 0 === \$context['loop']['revindex0'];
    }
}
\$_parent = \$context['_parent'];
unset(\$context['_seq'], \$context['_iterated'], \$context['k'], \$context['v'], \$context['_parent'], \$context['loop']);
\$context = array_intersect_key(\$context, \$_parent) + \$_parent;
EOF
        );

        $keyTarget = Apishka_Templater_Node_Expression_AssignName::apishka('k', 1);
        $valueTarget = Apishka_Templater_Node_Expression_AssignName::apishka('v', 1);
        $seq = Apishka_Templater_Node_Expression_Name::apishka('values', 1);
        $ifexpr = Apishka_Templater_Node_Expression_Constant::apishka(true, 1);
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1)), array(), 1);
        $else = null;
        $node = Apishka_Templater_Node_For::apishka($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, 1);
        $node->setAttribute('with_loop', true);

        $tests[] = array($node, <<<EOF
// line 1
\$context['_parent'] = \$context;
\$context['_seq'] = twig_ensure_traversable({$this->getVariableGetter('values')});
\$context['loop'] = array(
  'parent' => \$context['_parent'],
  'index0' => 0,
  'index'  => 1,
  'first'  => true,
);
foreach (\$context['_seq'] as \$context["k"] => \$context["v"]) {
    if (true) {
        echo {$this->getVariableGetter('foo')};
        ++\$context['loop']['index0'];
        ++\$context['loop']['index'];
        \$context['loop']['first'] = false;
    }
}
\$_parent = \$context['_parent'];
unset(\$context['_seq'], \$context['_iterated'], \$context['k'], \$context['v'], \$context['_parent'], \$context['loop']);
\$context = array_intersect_key(\$context, \$_parent) + \$_parent;
EOF
        );

        $keyTarget = Apishka_Templater_Node_Expression_AssignName::apishka('k', 1);
        $valueTarget = Apishka_Templater_Node_Expression_AssignName::apishka('v', 1);
        $seq = Apishka_Templater_Node_Expression_Name::apishka('values', 1);
        $ifexpr = null;
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1)), array(), 1);
        $else = Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Name::apishka('foo', 1), 1);
        $node = Apishka_Templater_Node_For::apishka($keyTarget, $valueTarget, $seq, $ifexpr, $body, $else, 1);
        $node->setAttribute('with_loop', true);

        $tests[] = array($node, <<<EOF
// line 1
\$context['_parent'] = \$context;
\$context['_seq'] = twig_ensure_traversable({$this->getVariableGetter('values')});
\$context['_iterated'] = false;
\$context['loop'] = array(
  'parent' => \$context['_parent'],
  'index0' => 0,
  'index'  => 1,
  'first'  => true,
);
if (is_array(\$context['_seq']) || (is_object(\$context['_seq']) && \$context['_seq'] instanceof Countable)) {
    \$length = count(\$context['_seq']);
    \$context['loop']['revindex0'] = \$length - 1;
    \$context['loop']['revindex'] = \$length;
    \$context['loop']['length'] = \$length;
    \$context['loop']['last'] = 1 === \$length;
}
foreach (\$context['_seq'] as \$context["k"] => \$context["v"]) {
    echo {$this->getVariableGetter('foo')};
    \$context['_iterated'] = true;
    ++\$context['loop']['index0'];
    ++\$context['loop']['index'];
    \$context['loop']['first'] = false;
    if (isset(\$context['loop']['length'])) {
        --\$context['loop']['revindex0'];
        --\$context['loop']['revindex'];
        \$context['loop']['last'] = 0 === \$context['loop']['revindex0'];
    }
}
if (!\$context['_iterated']) {
    echo {$this->getVariableGetter('foo')};
}
\$_parent = \$context['_parent'];
unset(\$context['_seq'], \$context['_iterated'], \$context['k'], \$context['v'], \$context['_parent'], \$context['loop']);
\$context = array_intersect_key(\$context, \$_parent) + \$_parent;
EOF
        );

        return $tests;
    }
}
