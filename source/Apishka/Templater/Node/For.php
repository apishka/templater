<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a for node.
 */
class Apishka_Templater_Node_For extends Apishka_Templater_NodeAbstract
{
    private $loop;

    public function __construct(Apishka_Templater_Node_Expression_AssignName $keyTarget, Apishka_Templater_Node_Expression_AssignName $valueTarget, Apishka_Templater_Node_ExpressionAbstract $seq, Apishka_Templater_Node_ExpressionAbstract $ifexpr = null, Apishka_Templater_NodeAbstract $body, Apishka_Templater_NodeAbstract $else = null, $lineno, $tag = null)
    {
        $body = Apishka_Templater_Node::apishka(array($body, $this->loop = Apishka_Templater_Node_ForLoop::apishka($lineno, $tag)));

        if (null !== $ifexpr) {
            $body = Apishka_Templater_Node_If::apishka(Apishka_Templater_Node::apishka(array($ifexpr, $body)), null, $lineno, $tag);
        }

        parent::__construct(array('key_target' => $keyTarget, 'value_target' => $valueTarget, 'seq' => $seq, 'body' => $body, 'else' => $else), array('with_loop' => true, 'ifexpr' => null !== $ifexpr), $lineno, $tag);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$context['_parent'] = \$context;\n")
            ->write("\$context['_seq'] = twig_ensure_traversable(")
            ->subcompile($this->getNode('seq'))
            ->raw(");\n")
        ;

        if (null !== $this->getNode('else')) {
            $compiler->write("\$context['_iterated'] = false;\n");
        }

        if ($this->getAttribute('with_loop')) {
            $compiler
                ->write("\$context['loop'] = array(\n")
                ->write("  'parent' => \$context['_parent'],\n")
                ->write("  'index0' => 0,\n")
                ->write("  'index'  => 1,\n")
                ->write("  'first'  => true,\n")
                ->write(");\n")
            ;

            if (!$this->getAttribute('ifexpr')) {
                $compiler
                    ->write("if (is_array(\$context['_seq']) || (is_object(\$context['_seq']) && \$context['_seq'] instanceof Countable)) {\n")
                    ->indent()
                    ->write("\$length = count(\$context['_seq']);\n")
                    ->write("\$context['loop']['revindex0'] = \$length - 1;\n")
                    ->write("\$context['loop']['revindex'] = \$length;\n")
                    ->write("\$context['loop']['length'] = \$length;\n")
                    ->write("\$context['loop']['last'] = 1 === \$length;\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }
        }

        $this->loop->setAttribute('else', null !== $this->getNode('else'));
        $this->loop->setAttribute('with_loop', $this->getAttribute('with_loop'));
        $this->loop->setAttribute('ifexpr', $this->getAttribute('ifexpr'));

        $compiler
            ->write("foreach (\$context['_seq'] as ")
            ->subcompile($this->getNode('key_target'))
            ->raw(' => ')
            ->subcompile($this->getNode('value_target'))
            ->raw(") {\n")
            ->indent()
            ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("}\n")
        ;

        if (null !== $this->getNode('else')) {
            $compiler
                ->write("if (!\$context['_iterated']) {\n")
                ->indent()
                ->subcompile($this->getNode('else'))
                ->outdent()
                ->write("}\n")
            ;
        }

        $compiler->write("\$_parent = \$context['_parent'];\n");

        // remove some "private" loop variables (needed for nested loops)
        $compiler->write('unset($context[\'_seq\'], $context[\'_iterated\'], $context[\'' . $this->getNode('key_target')->getAttribute('name') . '\'], $context[\'' . $this->getNode('value_target')->getAttribute('name') . '\'], $context[\'_parent\'], $context[\'loop\']);' . "\n");

        // keep the values set in the inner context for variables defined in the outer context
        $compiler->write("\$context = array_intersect_key(\$context, \$_parent) + \$_parent;\n");
    }
}
