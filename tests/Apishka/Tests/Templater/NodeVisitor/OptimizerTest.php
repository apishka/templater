<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Tests_Templater_NodeVisitor_OptimizerTest extends PHPUnit_Framework_TestCase
{
    public function testRenderBlockOptimizer()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));

        $stream = $env->parse($env->tokenize('{{ block("foo") }}', 'index'));

        $node = $stream->getNode('body')->getNode(0);

        $this->assertEquals('Apishka_Templater_Node_Expression_BlockReference', get_class($node));
        $this->assertTrue($node->getAttribute('output'));
    }

    public function testRenderParentBlockOptimizer()
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false, 'autoescape' => false));

        $stream = $env->parse($env->tokenize('{% extends "foo" %}{% block content %}{{ parent() }}{% endblock %}', 'index'));

        $node = $stream->getNode('blocks')->getNode('content')->getNode(0)->getNode('body');

        $this->assertEquals('Apishka_Templater_Node_Expression_Parent', get_class($node));
        $this->assertTrue($node->getAttribute('output'));
    }

    /**
     * @dataProvider getTestsForForOptimizer
     */
    public function testForOptimizer($template, $expected)
    {
        $env = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array('cache' => false));

        $stream = $env->parse($env->tokenize($template, 'index'));

        foreach ($expected as $target => $withLoop) {
            $this->assertTrue($this->checkForConfiguration($stream, $target, $withLoop), sprintf('variable %s is %soptimized', $target, $withLoop ? 'not ' : ''));
        }
    }

    public function getTestsForForOptimizer()
    {
        return array(
            array('{% for i in foo %}{% endfor %}', array('i' => false)),

            array('{% for i in foo %}{{ loop.index }}{% endfor %}', array('i' => true)),

            array('{% for i in foo %}{% for j in foo %}{% endfor %}{% endfor %}', array('i' => false, 'j' => false)),

            array('{% for i in foo %}{% for j in foo %}{{ loop.index }}{% endfor %}{% endfor %}', array('i' => false, 'j' => true)),

            array('{% for i in foo %}{% for j in foo %}{{ loop.parent.loop.index }}{% endfor %}{% endfor %}', array('i' => true, 'j' => true)),

            array('{% for i in foo %}{% set l = loop %}{% for j in foo %}{{ l.index }}{% endfor %}{% endfor %}', array('i' => true, 'j' => false)),

            array('{% for i in foo %}{% for j in foo %}{{ foo.parent.loop.index }}{% endfor %}{% endfor %}', array('i' => false, 'j' => false)),

            array('{% for i in foo %}{% for j in foo %}{{ loop["parent"].loop.index }}{% endfor %}{% endfor %}', array('i' => true, 'j' => true)),
        );
    }

    public function checkForConfiguration(Apishka_Templater_NodeAbstract $node = null, $target, $withLoop)
    {
        if (null === $node) {
            return;
        }

        foreach ($node as $n) {
            if ($n instanceof Apishka_Templater_Node_For) {
                if ($target === $n->getNode('value_target')->getAttribute('name')) {
                    return $withLoop == $n->getAttribute('with_loop');
                }
            }

            $ret = $this->checkForConfiguration($n, $target, $withLoop);
            if (null !== $ret) {
                return $ret;
            }
        }
    }
}
