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
 * Represents a module node.
 *
 * Consider this class as being final. If you need to customize the behavior of
 * the generated class, consider adding nodes to the following nodes: display_start,
 * display_end, constructor_start, constructor_end, and class_end.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Module extends Apishka_Templater_NodeAbstract
{
    public function __construct(Apishka_Templater_NodeAbstract $body, Apishka_Templater_Node_ExpressionAbstract $parent = null, Apishka_Templater_NodeAbstract $blocks, Apishka_Templater_NodeAbstract $macros, Apishka_Templater_NodeAbstract $traits, $embeddedTemplates, $filename)
    {
        // embedded templates are set as attributes so that they are only visited once by the visitors
        parent::__construct(array(
            'parent'            => $parent,
            'body'              => $body,
            'blocks'            => $blocks,
            'macros'            => $macros,
            'traits'            => $traits,
            'display_start'     => Apishka_Templater_Node::apishka(),
            'display_end'       => Apishka_Templater_Node::apishka(),
            'constructor_start' => Apishka_Templater_Node::apishka(),
            'constructor_end'   => Apishka_Templater_Node::apishka(),
            'class_end'         => Apishka_Templater_Node::apishka(),
        ), array(
            'filename'           => $filename,
            'index'              => null,
            'embedded_templates' => $embeddedTemplates,
        ), 1);
    }

    public function setIndex($index)
    {
        $this->setAttribute('index', $index);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $this->compileTemplate($compiler);

        foreach ($this->getAttribute('embedded_templates') as $template) {
            $compiler->subcompile($template);
        }
    }

    protected function compileTemplate(Apishka_Templater_Compiler $compiler)
    {
        if (!$this->getAttribute('index')) {
            $compiler->write('<?php');
        }

        $this->compileClassHeader($compiler);

        if (
            count($this->getNode('blocks'))
            || count($this->getNode('traits'))
            || null === $this->getNode('parent')
            || $this->getNode('parent') instanceof Apishka_Templater_Node_Expression_Constant
            || count($this->getNode('constructor_start'))
            || count($this->getNode('constructor_end'))
        ) {
            $this->compileConstructor($compiler);
        }

        $this->compileApishkaName($compiler);

        $this->compileGetParent($compiler);

        $this->compileDisplay($compiler);

        $compiler->subcompile($this->getNode('blocks'));

        $this->compileMacros($compiler);

        $this->compileGetTemplateName($compiler);

        $this->compileIsTraitable($compiler);

        $this->compileDebugInfo($compiler);

        $this->compileClassFooter($compiler);
    }

    /**
     * Compile apishka name
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    protected function compileApishkaName(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("public function getSupportedNames()\n", "{\n")
            ->indent()
            ->write('return array(' . PHP_EOL)
            ->indent()
            ->write(sprintf("'%s',", $this->getAttribute('filename')) . PHP_EOL)
            ->outdent()
            ->write(');' . PHP_EOL)
            ->outdent()
            ->write('}' . PHP_EOL . PHP_EOL)
        ;
    }

    protected function compileGetParent(Apishka_Templater_Compiler $compiler)
    {
        if (null === $parent = $this->getNode('parent')) {
            return;
        }

        $compiler
            ->write("protected function doGetParent(array \$context)\n", "{\n")
            ->indent()
            ->addDebugInfo($parent)
            ->write('return ')
        ;

        if ($parent instanceof Apishka_Templater_Node_Expression_Constant) {
            $compiler->subcompile($parent);
        } else {
            $compiler
                ->raw('$this->loadTemplate(')
                ->subcompile($parent)
                ->raw(', ')
                ->repr($compiler->getFilename())
                ->raw(', ')
                ->repr($this->getNode('parent')->getLine())
                ->raw(')')
            ;
        }

        $compiler
            ->raw(";\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }

    protected function compileClassHeader(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("\n\n")
            // if the filename contains */, add a blank to avoid a PHP parse error
            ->write('/* ' . str_replace('*/', '* /', $this->getAttribute('filename')) . " */\n")
            ->write('class ' . $compiler->getEnvironment()->getTemplateClass($this->getAttribute('filename'), $this->getAttribute('index')))
            ->raw(sprintf(" extends %s\n", $compiler->getEnvironment()->getBaseTemplateClass()))
            ->write("{\n")
            ->indent()
        ;
    }

    protected function compileConstructor(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("public function __construct(Apishka_Templater_Environment \$env)\n", "{\n")
            ->indent()
            ->subcompile($this->getNode('constructor_start'))
            ->write("parent::__construct(\$env);\n\n")
        ;

        // parent
        if (null === $parent = $this->getNode('parent')) {
            $compiler->write("\$this->parent = false;\n\n");
        } elseif ($parent instanceof Apishka_Templater_Node_Expression_Constant) {
            $compiler
                ->addDebugInfo($parent)
                ->write('$this->parent = $this->loadTemplate(')
                ->subcompile($parent)
                ->raw(', ')
                ->repr($compiler->getFilename())
                ->raw(', ')
                ->repr($this->getNode('parent')->getLine())
                ->raw(");\n")
            ;
        }

        $countTraits = count($this->getNode('traits'));
        if ($countTraits) {
            // traits
            foreach ($this->getNode('traits') as $i => $trait) {
                $node = $trait->getNode('template');

                $compiler
                    ->write(sprintf('$_trait_%s = $this->loadTemplate(', $i))
                    ->subcompile($node)
                    ->raw(', ')
                    ->repr($compiler->getFilename())
                    ->raw(', ')
                    ->repr($node->getLine())
                    ->raw(");\n")
                ;

                $compiler
                    ->addDebugInfo($trait->getNode('template'))
                    ->write(sprintf("if (!\$_trait_%s->isTraitable()) {\n", $i))
                    ->indent()
                    ->write("throw new Apishka_Templater_Error_Runtime('Template \"'.")
                    ->subcompile($trait->getNode('template'))
                    ->raw(".'\" cannot be used as a trait.');\n")
                    ->outdent()
                    ->write("}\n")
                    ->write(sprintf("\$_trait_%s_blocks = \$_trait_%s->getBlocks();\n\n", $i, $i))
                ;

                foreach ($trait->getNode('targets') as $key => $value) {
                    $compiler
                        ->write(sprintf('if (!isset($_trait_%s_blocks[', $i))
                        ->string($key)
                        ->raw("])) {\n")
                        ->indent()
                        ->write("throw new Apishka_Templater_Error_Runtime(sprintf('Block ")
                        ->string($key)
                        ->raw(' is not defined in trait ')
                        ->subcompile($trait->getNode('template'))
                        ->raw(".'));\n")
                        ->outdent()
                        ->write("}\n\n")

                        ->write(sprintf('$_trait_%s_blocks[', $i))
                        ->subcompile($value)
                        ->raw(sprintf('] = $_trait_%s_blocks[', $i))
                        ->string($key)
                        ->raw(sprintf(']; unset($_trait_%s_blocks[', $i))
                        ->string($key)
                        ->raw("]);\n\n")
                    ;
                }
            }

            if ($countTraits > 1) {
                $compiler
                    ->write("\$this->traits = array_merge(\n")
                    ->indent()
                ;

                for ($i = 0; $i < $countTraits; ++$i) {
                    $compiler
                        ->write(sprintf('$_trait_%s_blocks' . ($i == $countTraits - 1 ? '' : ',') . "\n", $i))
                    ;
                }

                $compiler
                    ->outdent()
                    ->write(");\n\n")
                ;
            } else {
                $compiler
                    ->write("\$this->traits = \$_trait_0_blocks;\n\n")
                ;
            }

            $compiler
                ->write("\$this->blocks = array_merge(\n")
                ->indent()
                ->write("\$this->traits,\n")
                ->write("array(\n")
            ;
        } else {
            $compiler
                ->write("\$this->blocks = array(\n")
            ;
        }

        // blocks
        $compiler
            ->indent()
        ;

        foreach ($this->getNode('blocks') as $name => $node) {
            $compiler
                ->write(sprintf("'%s' => array(\$this, 'block_%s'),\n", $name, $name))
            ;
        }

        if ($countTraits) {
            $compiler
                ->outdent()
                ->write(")\n")
            ;
        }

        $compiler
            ->outdent()
            ->write(");\n")
            ->outdent()
            ->subcompile($this->getNode('constructor_end'))
            ->write("}\n\n")
        ;
    }

    protected function compileDisplay(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("protected function doDisplay(array \$context, array \$blocks = array())\n", "{\n")
            ->indent()
            ->subcompile($this->getNode('display_start'))
            ->subcompile($this->getNode('body'))
        ;

        if (null !== $parent = $this->getNode('parent')) {
            $compiler->addDebugInfo($parent);
            if ($parent instanceof Apishka_Templater_Node_Expression_Constant) {
                $compiler->write('$this->parent');
            } else {
                $compiler->write('$this->getParent($context)');
            }
            $compiler->raw("->display(\$context, array_merge(\$this->blocks, \$blocks));\n");
        }

        $compiler
            ->subcompile($this->getNode('display_end'))
            ->outdent()
            ->write("}\n\n")
        ;
    }

    protected function compileClassFooter(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->subcompile($this->getNode('class_end'))
            ->outdent()
            ->write("}\n")
        ;
    }

    protected function compileMacros(Apishka_Templater_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('macros'));
    }

    protected function compileGetTemplateName(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("public function getTemplateName()\n", "{\n")
            ->indent()
            ->write('return ')
            ->repr($this->getAttribute('filename'))
            ->raw(";\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }

    protected function compileIsTraitable(Apishka_Templater_Compiler $compiler)
    {
        // A template can be used as a trait if:
        //   * it has no parent
        //   * it has no macros
        //   * it has no body
        //
        // Put another way, a template can be used as a trait if it
        // only contains blocks and use statements.
        $traitable = null === $this->getNode('parent') && 0 === count($this->getNode('macros'));
        if ($traitable) {
            if ($this->getNode('body') instanceof Apishka_Templater_Node_Body) {
                $nodes = $this->getNode('body')->getNode(0);
            } else {
                $nodes = $this->getNode('body');
            }

            if (!count($nodes)) {
                $nodes = Apishka_Templater_Node::apishka(array($nodes));
            }

            foreach ($nodes as $node) {
                if (!count($node)) {
                    continue;
                }

                if ($node instanceof Apishka_Templater_Node_Text && ctype_space($node->getAttribute('data'))) {
                    continue;
                }

                if ($node instanceof Apishka_Templater_Node_BlockReference) {
                    continue;
                }

                $traitable = false;
                break;
            }
        }

        if ($traitable) {
            return;
        }

        $compiler
            ->write("public function isTraitable()\n", "{\n")
            ->indent()
            ->write(sprintf("return %s;\n", $traitable ? 'true' : 'false'))
            ->outdent()
            ->write("}\n\n")
        ;
    }

    protected function compileDebugInfo(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("public function getDebugInfo()\n", "{\n")
            ->indent()
            ->write(sprintf("return %s;\n", str_replace("\n", '', var_export(array_reverse($compiler->getDebugInfo(), true), true))))
            ->outdent()
            ->write("}\n")
        ;
    }
}
