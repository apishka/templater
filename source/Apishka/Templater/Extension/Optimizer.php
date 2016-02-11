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
 * Apishka templater extension optimizer
 *
 * @uses Apishka_Templater_ExtensionAbstract
 *
 * @author Evgeny Reykh <evgeny@reykh.com>
 */

class Apishka_Templater_Extension_Optimizer extends Apishka_Templater_ExtensionAbstract
{
    private $optimizers;

    public function __construct($optimizers = -1)
    {
        $this->optimizers = $optimizers;
    }

    public function getNodeVisitors()
    {
        return array(
            Apishka_Templater_NodeVisitor_Optimizer::apishka($this->optimizers),
        );
    }

    public function getName()
    {
        return 'optimizer';
    }
}
