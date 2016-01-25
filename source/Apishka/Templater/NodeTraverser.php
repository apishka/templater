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
 * Apishka_Templater_NodeTraverser is a node traverser.
 *
 * It visits all nodes and their children and calls the given visitor for each.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_NodeTraverser
{
    private $env;
    private $visitors = array();

    /**
     * Constructor.
     *
     * @param Apishka_Templater_Environment            $env      A Apishka_Templater_Environment instance
     * @param Apishka_Templater_NodeVisitorInterface[] $visitors An array of Apishka_Templater_NodeVisitorInterface instances
     */
    public function __construct(Apishka_Templater_Environment $env, array $visitors = array())
    {
        $this->env = $env;
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds a visitor.
     *
     * @param Apishka_Templater_NodeVisitorInterface $visitor A Apishka_Templater_NodeVisitorInterface instance
     */
    public function addVisitor(Apishka_Templater_NodeVisitorInterface $visitor)
    {
        if (!isset($this->visitors[$visitor->getPriority()])) {
            $this->visitors[$visitor->getPriority()] = array();
        }

        $this->visitors[$visitor->getPriority()][] = $visitor;
    }

    /**
     * Traverses a node and calls the registered visitors.
     *
     * @param Apishka_Templater_Node $node A Apishka_Templater_Node instance
     *
     * @return Apishka_Templater_Node
     */
    public function traverse(Apishka_Templater_Node $node)
    {
        ksort($this->visitors);
        foreach ($this->visitors as $visitors) {
            foreach ($visitors as $visitor) {
                $node = $this->traverseForVisitor($visitor, $node);
            }
        }

        return $node;
    }

    private function traverseForVisitor(Apishka_Templater_NodeVisitorInterface $visitor, Apishka_Templater_Node $node = null)
    {
        if (null === $node) {
            return;
        }

        $node = $visitor->enterNode($node, $this->env);

        foreach ($node as $k => $n) {
            if (false !== $n = $this->traverseForVisitor($visitor, $n)) {
                $node->setNode($k, $n);
            } else {
                $node->removeNode($k);
            }
        }

        return $visitor->leaveNode($node, $this->env);
    }
}
