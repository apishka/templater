<?php

/*
 * This file is part of Twig.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents an embed node.
 *
 * @easy-extend-base
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Embed extends Apishka_Templater_Node_Include
{
    // we don't inject the module to avoid node visitors to traverse it twice (as it will be already visited in the main module)
    public function __construct($filename, $index, Apishka_Templater_Node_ExpressionAbstract $variables = null, $only = false, $ignoreMissing = false, $lineno, $tag = null)
    {
        parent::__construct(Apishka_Templater_Node_Expression_Constant::apishka('not_used', $lineno), $variables, $only, $ignoreMissing, $lineno, $tag);

        $this->setAttribute('filename', $filename);
        $this->setAttribute('index', $index);
    }

    protected function addGetTemplate(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write('$this->loadTemplate(')
            ->string($this->getAttribute('filename'))
            ->raw(', ')
            ->repr($compiler->getFilename())
            ->raw(', ')
            ->repr($this->getLine())
            ->raw(', ')
            ->string($this->getAttribute('index'))
            ->raw(')')
        ;
    }
}