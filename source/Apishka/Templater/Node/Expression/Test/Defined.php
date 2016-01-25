<?php

/*
 * This file is part of Twig.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Checks if a variable is defined in the current context.
 *
 * @easy-extend-base
 *
 * <pre>
 * {# defined works with variable names and variable attributes #}
 * {% if foo is defined %}
 *     {# ... #}
 * {% endif %}
 * </pre>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Expression_Test_Defined extends Apishka_Templater_Node_Expression_Test
{
    public function __construct(Apishka_Templater_NodeAbstract $node, $name, Apishka_Templater_NodeAbstract $arguments = null, $lineno)
    {
        parent::__construct($node, $name, $arguments, $lineno);

        if ($node instanceof Apishka_Templater_Node_Expression_Name) {
            $node->setAttribute('is_defined_test', true);
        } elseif ($node instanceof Apishka_Templater_Node_Expression_GetAttr) {
            $node->setAttribute('is_defined_test', true);

            $this->changeIgnoreStrictCheck($node);
        } else {
            throw new Apishka_Templater_Error_Syntax('The "defined" test only works with simple variables.', $this->getLine());
        }
    }

    private function changeIgnoreStrictCheck(Apishka_Templater_Node_Expression_GetAttr $node)
    {
        $node->setAttribute('ignore_strict_check', true);

        if ($node->getNode('node') instanceof Apishka_Templater_Node_Expression_GetAttr) {
            $this->changeIgnoreStrictCheck($node->getNode('node'));
        }
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('node'));
    }
}
