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
 * Represents a block call node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Expression_BlockReference extends Apishka_Templater_Node_ExpressionAbstract
{
    public function __construct(Apishka_Templater_NodeAbstract $name, Apishka_Templater_NodeAbstract $args, $asString = false, $lineno, $tag = null)
    {
        parent::__construct(
            array(
                'name' => $name,
                'args' => $args,
            ),
            array(
                'as_string' => $asString,
                'output'    => false,
            ),
            $lineno,
            $tag
        );
    }

    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        if ($this->getAttribute('as_string'))
            $compiler->raw('(string) ');

        $args = $this->getNode('args');

        if ($this->getAttribute('output'))
        {
            $compiler
                ->addDebugInfo($this)
                ->write('$this->displayBlock(')
                ->subcompile($this->getNode('name'))
                ->raw(', ')
            ;

            $this->compileContext($compiler, $args);

            $compiler
                ->write('$blocks);' . PHP_EOL)
            ;
        }
        else
        {
            $compiler
                ->raw('$this->renderBlock(')
                ->subcompile($this->getNode('name'))
                ->raw(', ')
            ;

            $this->compileContext($compiler, $args);

            $compiler
                ->write('$blocks)')
            ;
        }
    }

    /**
     * Compile args
     *
     * @param Apishka_Templater_Compiler     $compiler
     * @param Apishka_Templater_NodeAbstract $args
     */

    protected function compileContext(Apishka_Templater_Compiler $compiler, Apishka_Templater_NodeAbstract $args)
    {
        if (!count($args))
        {
            $compiler
                ->raw('$context, ')
            ;

            return;
        }

        $compiler
            ->raw('array_replace(')
            ->raw('$context, ')
            ->raw('array(')
        ;

        foreach ($args as $name => $arg)
        {
            $compiler
                ->write('')
                ->string($name)
                ->raw(' => ')
                ->subcompile($arg)
                ->raw(',')
            ;
        }

        $compiler
            ->raw(')')
            ->raw('), ')
        ;
    }

    /**
     * Is safe all
     *
     * @return bool
     */

    public function isSafeAll()
    {
        return true;
    }

    /**
     * Is output supported
     *
     * @return bool
     */

    public function isOutputSupported()
    {
        return true;
    }
}
