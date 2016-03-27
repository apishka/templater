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
 * Apishka_Templater_NodeVisitor_Escaper implements output escaping.
 */

class Apishka_Templater_NodeVisitor_Escaper extends Apishka_Templater_BaseNodeVisitorAbstract
{
    private $statusStack = array();
    private $blocks = array();
    private $safeAnalysis;
    private $traverser;
    private $defaultStrategy = false;
    private $safeVars = array();

    /**
     * Construct
     */

    public function __construct()
    {
        $this->safeAnalysis = Apishka_Templater_NodeVisitor_SafeAnalysis::apishka();
    }

    /**
     * {@inheritdoc}
     */

    protected function doEnterNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        if ($node instanceof Apishka_Templater_Node_Module) {
            if ($env->hasExtension('escaper') && $defaultStrategy = $env->getExtension('escaper')->getDefaultStrategy($node->getAttribute('filename'))) {
                $this->defaultStrategy = $defaultStrategy;
            }
            $this->safeVars = array();
        } elseif ($node instanceof Apishka_Templater_Node_AutoEscape) {
            $this->statusStack[] = $node->getAttribute('value');
        } elseif ($node instanceof Apishka_Templater_Node_Block) {
            $this->statusStack[] = isset($this->blocks[$node->getAttribute('name')]) ? $this->blocks[$node->getAttribute('name')] : $this->needEscaping($env);
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        if ($node instanceof Apishka_Templater_Node_Module) {
            $this->defaultStrategy = false;
            $this->safeVars = array();
        } elseif ($node instanceof Apishka_Templater_Node_Expression_Filter) {
            return $this->preEscapeFilterNode($node, $env);
        } elseif ($node instanceof Apishka_Templater_Node_Print) {
            return $this->escapePrintNode($node, $env, $this->needEscaping($env));
        }

        if ($node instanceof Apishka_Templater_Node_AutoEscape || $node instanceof Apishka_Templater_Node_Block) {
            array_pop($this->statusStack);
        } elseif ($node instanceof Apishka_Templater_Node_BlockReference) {
            $this->blocks[$node->getAttribute('name')] = $this->needEscaping($env);
        }

        return $node;
    }

    private function escapePrintNode(Apishka_Templater_Node_Print $node, Apishka_Templater_Environment $env, $type)
    {
        if (false === $type) {
            return $node;
        }

        $expression = $node->getNode('expr');

        if ($this->isSafeFor($type, $expression, $env)) {
            return $node;
        }

        $class = get_class($node);

        return new $class(
            $this->getEscaperFilter($type, $expression),
            $node->getLine()
        );
    }

    private function preEscapeFilterNode(Apishka_Templater_Node_Expression_Filter $filter, Apishka_Templater_Environment $env)
    {
        $name = $filter->getNode('filter')->getAttribute('value');

        $type = $env->getFilter($name)->getPreEscape();
        if (null === $type) {
            return $filter;
        }

        $node = $filter->getNode('node');
        if ($this->isSafeFor($type, $node, $env)) {
            return $filter;
        }

        $filter->setNode('node', $this->getEscaperFilter($type, $node));

        return $filter;
    }

    private function isSafeFor($type, Apishka_Templater_NodeAbstract $expression, $env)
    {
        $safe = $this->safeAnalysis->getSafe($expression);

        if (null === $safe) {
            if (null === $this->traverser) {
                $this->traverser = new Apishka_Templater_NodeTraverser($env, array($this->safeAnalysis));
            }

            $this->safeAnalysis->setSafeVars($this->safeVars);

            $this->traverser->traverse($expression);
            $safe = $this->safeAnalysis->getSafe($expression);
        }

        return in_array($type, $safe) || in_array('all', $safe);
    }

    private function needEscaping(Apishka_Templater_Environment $env)
    {
        if (count($this->statusStack)) {
            return $this->statusStack[count($this->statusStack) - 1];
        }

        return $this->defaultStrategy ? $this->defaultStrategy : false;
    }

    private function getEscaperFilter($type, Apishka_Templater_NodeAbstract $node)
    {
        $line = $node->getLine();
        $name = Apishka_Templater_Node_Expression_Constant::apishka('escape', $line);
        $args = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka((string) $type, $line), Apishka_Templater_Node_Expression_Constant::apishka(null, $line), Apishka_Templater_Node_Expression_Constant::apishka(true, $line)));

        return Apishka_Templater_Node_Expression_Filter::apishka($node, $name, $args, $line);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
