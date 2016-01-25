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
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Profiler_NodeVisitor_Profiler extends Apishka_Templater_BaseNodeVisitor
{
    private $extensionName;

    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        if ($node instanceof Apishka_Templater_Node_Module) {
            $varName = $this->getVarName();
            $node->setNode('display_start', Apishka_Templater_Node::apishka(array(new Apishka_Templater_Profiler_Node_EnterProfile($this->extensionName, Apishka_Templater_Profiler_Profile::TEMPLATE, $node->getAttribute('filename'), $varName), $node->getNode('display_start'))));
            $node->setNode('display_end', Apishka_Templater_Node::apishka(array(new Apishka_Templater_Profiler_Node_LeaveProfile($varName), $node->getNode('display_end'))));
        } elseif ($node instanceof Apishka_Templater_Node_Block) {
            $varName = $this->getVarName();
            $node->setNode('body', Apishka_Templater_Node_Body::apishka(array(
                new Apishka_Templater_Profiler_Node_EnterProfile($this->extensionName, Apishka_Templater_Profiler_Profile::BLOCK, $node->getAttribute('name'), $varName),
                $node->getNode('body'),
                new Apishka_Templater_Profiler_Node_LeaveProfile($varName),
            )));
        } elseif ($node instanceof Apishka_Templater_Node_Macro) {
            $varName = $this->getVarName();
            $node->setNode('body', Apishka_Templater_Node_Body::apishka(array(
                new Apishka_Templater_Profiler_Node_EnterProfile($this->extensionName, Apishka_Templater_Profiler_Profile::MACRO, $node->getAttribute('name'), $varName),
                $node->getNode('body'),
                new Apishka_Templater_Profiler_Node_LeaveProfile($varName),
            )));
        }

        return $node;
    }

    private function getVarName()
    {
        return sprintf('__internal_%s', hash('sha256', uniqid(mt_rand(), true), false));
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }
}
