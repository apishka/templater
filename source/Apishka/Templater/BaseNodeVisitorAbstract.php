<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka_Templater_BaseNodeVisitorAbstract can be used to make node visitors compatible with Twig 1.x and 2.x.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */

abstract class Apishka_Templater_BaseNodeVisitorAbstract implements Apishka_Templater_NodeVisitorInterface
{
    /**
     * {@inheritdoc}
     */

    final public function enterNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        return $this->doEnterNode($node, $env);
    }

    /**
     * {@inheritdoc}
     */

    final public function leaveNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        return $this->doLeaveNode($node, $env);
    }

    /**
     * Called before child nodes are visited.
     *
     * @param Apishka_Templater_Node        $node The node to visit
     * @param Apishka_Templater_Environment $env  The Twig environment instance
     *
     * @return Apishka_Templater_Node The modified node
     */

    abstract protected function doEnterNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env);

    /**
     * Called after child nodes are visited.
     *
     * @param Apishka_Templater_Node        $node The node to visit
     * @param Apishka_Templater_Environment $env  The Twig environment instance
     *
     * @return Apishka_Templater_Node|false The modified node or false if the node must be removed
     */

    abstract protected function doLeaveNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env);
}
