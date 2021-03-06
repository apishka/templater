<?php

/*
 * This file is part of Twig.
 *
 * (c) 2015 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a profile enter node.
 */
class Apishka_Templater_Profiler_Node_EnterProfile extends Apishka_Templater_NodeAbstract
{
    public function __construct($extensionName, $type, $name, $varName)
    {
        parent::__construct(array(), array('extension_name' => $extensionName, 'name' => $name, 'type' => $type, 'var_name' => $varName));
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write(sprintf('$%s = $this->env->getExtension(', $this->getAttribute('var_name')))
            ->repr($this->getAttribute('extension_name'))
            ->raw(");\n")
            ->write(sprintf('$%s->enter($%s = new Apishka_Templater_Profiler_Profile($this->getTemplateName(), ', $this->getAttribute('var_name'), $this->getAttribute('var_name') . '_prof'))
            ->repr($this->getAttribute('type'))
            ->raw(', ')
            ->repr($this->getAttribute('name'))
            ->raw("));\n\n")
        ;
    }
}
