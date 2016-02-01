<?php

/**
 * Apishka templater node expression action
 *
 * @uses Apishka_Templater_Node_ExpressionAbstract
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Expression_Action extends Apishka_Templater_Node_ExpressionAbstract
{
    /**
     * Construct
     *
     * @param Apishka_Templater_NodeAbstract $name
     * @param Apishka_Templater_NodeAbstract $args
     * @param int                            $lineno
     * @param mixed                          $tag
     */

    public function __construct(Apishka_Templater_NodeAbstract $name, Apishka_Templater_NodeAbstract $args, $lineno, $tag = null)
    {
        parent::__construct(
            array(
                'name' => $name,
                'args' => $args,
            ),
            array(
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
        $args = $this->getNode('args');

        if ($this->getAttribute('output'))
        {
            $compiler
                ->addDebugInfo($this)
                ->write('$this->displayAction(')
                ->subcompile($this->getNode('name'))
                ->raw(', ')
            ;

            $this->compileContext($compiler, $args);

            $compiler
                ->write(');' . PHP_EOL)
            ;
        }
        else
        {
            $compiler
                ->raw('$this->renderAction(')
                ->subcompile($this->getNode('name'))
                ->raw(', ')
            ;

            $this->compileContext($compiler, $args);

            $compiler
                ->write(')')
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
                ->raw('$context')
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
            ->raw(')')
        ;
    }
}
