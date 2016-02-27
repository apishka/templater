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
class Apishka_Templater_Node_Expression_GetAttr extends Apishka_Templater_Node_ExpressionAbstract
{
    public function __construct(Apishka_Templater_Node_ExpressionAbstract $node, Apishka_Templater_Node_ExpressionAbstract $attribute, Apishka_Templater_Node_ExpressionAbstract $arguments = null, $type, $lineno)
    {
        parent::__construct(array('node' => $node, 'attribute' => $attribute, 'arguments' => $arguments), array('type' => $type, 'disable_c_ext' => false), $lineno);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        if (function_exists('twig_template_get_attributes') && !$this->getAttribute('disable_c_ext')) {
            $compiler->raw('twig_template_get_attributes($this, ');
        } else {
            $compiler->raw('$this->getAttribute(');
        }

        $compiler->subcompile($this->getNode('node'));

        $compiler->raw(', ')->subcompile($this->getNode('attribute'));

        // only generate optional arguments when needed (to make generated code more readable)
        $needSecond = Apishka_Templater_TemplateAbstract::ANY_CALL !== $this->getAttribute('type');
        $needFirst = $needSecond || null !== $this->getNode('arguments');

        if ($needFirst) {
            if (null !== $this->getNode('arguments')) {
                $compiler->raw(', ')->subcompile($this->getNode('arguments'));
            } else {
                $compiler->raw(', array()');
            }
        }

        if ($needSecond) {
            $compiler->raw(', ')->repr($this->getAttribute('type'));
        }

        $compiler->raw(')');
    }
}
