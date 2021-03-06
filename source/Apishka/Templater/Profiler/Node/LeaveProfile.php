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
 * Represents a profile leave node.
 */
class Apishka_Templater_Profiler_Node_LeaveProfile extends Apishka_Templater_NodeAbstract
{
    public function __construct($varName)
    {
        parent::__construct(array(), array('var_name' => $varName));
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $compiler
            ->write("\n")
            ->write(sprintf("\$%s->leave(\$%s);\n\n", $this->getAttribute('var_name'), $this->getAttribute('var_name') . '_prof'))
        ;
    }
}
