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
 * Represents a sandbox node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Node_Sandbox extends Apishka_Templater_Node
{
    public function __construct(Apishka_Templater_Node $body, $lineno, $tag = null)
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$sandbox = \$this->env->getExtension('sandbox');\n")
            ->write("if (!\$alreadySandboxed = \$sandbox->isSandboxed()) {\n")
            ->indent()
            ->write("\$sandbox->enableSandbox();\n")
            ->outdent()
            ->write("}\n")
            ->subcompile($this->getNode('body'))
            ->write("if (!\$alreadySandboxed) {\n")
            ->indent()
            ->write("\$sandbox->disableSandbox();\n")
            ->outdent()
            ->write("}\n")
        ;
    }
}
