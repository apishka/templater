<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka_Templater_NodeVisitorInterface is the interface the all node visitor classes must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Apishka_Templater_NodeVisitorInterface
{
    /**
     * Called before child nodes are visited.
     *
     * @param Apishka_Templater_Node        $node The node to visit
     * @param Apishka_Templater_Environment $env  The Twig environment instance
     *
     * @return Apishka_Templater_Node The modified node
     */
    public function enterNode(Apishka_Templater_Node $node, Apishka_Templater_Environment $env);

    /**
     * Called after child nodes are visited.
     *
     * @param Apishka_Templater_Node        $node The node to visit
     * @param Apishka_Templater_Environment $env  The Twig environment instance
     *
     * @return Apishka_Templater_Node|false The modified node or false if the node must be removed
     */
    public function leaveNode(Apishka_Templater_Node $node, Apishka_Templater_Environment $env);

    /**
     * Returns the priority for this visitor.
     *
     * Priority should be between -10 and 10 (0 is the default).
     *
     * @return int The priority level
     */
    public function getPriority();
}
