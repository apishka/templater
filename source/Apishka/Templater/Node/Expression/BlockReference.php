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
                'output'    => false
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

        if ($this->getAttribute('output'))
        {
            $args = $this->getNode('args');
            if (count($args))
            {
                $compiler
                    ->addDebugInfo($this)
                    ->write('$this->displayBlock(' . PHP_EOL)
                    ->indent()
                        ->write('')
                        ->subcompile($this->getNode('name'))
                        ->raw(',' . PHP_EOL)
                        ->write('array_replace(' . PHP_EOL)
                        ->indent()
                            ->write('$context,' . PHP_EOL)
                            ->write('array(' . PHP_EOL)
                            ->indent()
                ;

                foreach ($args as $name => $arg)
                {
                    $compiler
                                ->write('')
                                ->string($name)
                                ->raw(' => ')
                                ->subcompile($arg)
                                ->raw(',' . PHP_EOL)
                    ;
                }

                $compiler
                            ->outdent()
                        ->write(')' . PHP_EOL)
                        ->outdent()
                    ->write('),' . PHP_EOL)
                    ->write('$blocks' . PHP_EOL)
                    ->outdent()
                    ->write(');' . PHP_EOL)
                    ->raw(PHP_EOL)
                ;
            }
            else
            {
                $compiler
                    ->addDebugInfo($this)
                    ->write('$this->displayBlock(')
                    ->subcompile($this->getNode('name'))
                    ->raw(', $context, $blocks);' . PHP_EOL)
                ;
            }
        }
        else
        {
            $compiler
                ->raw('$this->renderBlock(')
                ->subcompile($this->getNode('name'))
                ->raw(', $context, $blocks)')
            ;
        }
    }
}
