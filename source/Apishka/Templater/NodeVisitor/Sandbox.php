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
 * Apishka_Templater_NodeVisitor_Sandbox implements sandboxing.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_NodeVisitor_Sandbox extends Apishka_Templater_BaseNodeVisitor
{
    private $inAModule = false;
    private $tags;
    private $filters;
    private $functions;

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(Apishka_Templater_Node $node, Apishka_Templater_Environment $env)
    {
        if ($node instanceof Apishka_Templater_Node_Module) {
            $this->inAModule = true;
            $this->tags = array();
            $this->filters = array();
            $this->functions = array();

            return $node;
        } elseif ($this->inAModule) {
            // look for tags
            if ($node->getNodeTag() && !isset($this->tags[$node->getNodeTag()])) {
                $this->tags[$node->getNodeTag()] = $node;
            }

            // look for filters
            if ($node instanceof Apishka_Templater_Node_Expression_Filter && !isset($this->filters[$node->getNode('filter')->getAttribute('value')])) {
                $this->filters[$node->getNode('filter')->getAttribute('value')] = $node;
            }

            // look for functions
            if ($node instanceof Apishka_Templater_Node_Expression_Function && !isset($this->functions[$node->getAttribute('name')])) {
                $this->functions[$node->getAttribute('name')] = $node;
            }

            // wrap print to check __toString() calls
            if ($node instanceof Apishka_Templater_Node_Print) {
                return new Apishka_Templater_Node_SandboxedPrint($node->getNode('expr'), $node->getLine(), $node->getNodeTag());
            }
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(Apishka_Templater_Node $node, Apishka_Templater_Environment $env)
    {
        if ($node instanceof Apishka_Templater_Node_Module) {
            $this->inAModule = false;

            $node->setNode('display_start', new Apishka_Templater_Node(array(new Apishka_Templater_Node_CheckSecurity($this->filters, $this->tags, $this->functions), $node->getNode('display_start'))));
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
