<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka_Templater_Node_SandboxedPrint adds a check for the __toString() method
 * when the variable is an object and the sandbox is activated.
 *
 * When there is a simple Print statement, like {{ article }},
 * and if the sandbox is enabled, we need to check that the __toString()
 * method is allowed if 'article' is an object.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_SandboxedPrint extends Apishka_Templater_Node_Print
{
    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('echo $this->env->getExtension(\'sandbox\')->ensureToStringAllowed(')
            ->subcompile($this->getNode('expr'))
            ->raw(");\n")
        ;
    }

    /**
     * Removes node filters.
     *
     * This is mostly needed when another visitor adds filters (like the escaper one).
     *
     * @param Apishka_Templater_Node $node A Node
     *
     * @return Apishka_Templater_Node
     */
    private function removeNodeFilter($node)
    {
        if ($node instanceof Apishka_Templater_Node_Expression_Filter) {
            return $this->removeNodeFilter($node->getNode('node'));
        }

        return $node;
    }
}
