<?php

/*
 * This file is part of Twig.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Used by Apishka_Templater_Environment as a staging area.
 *
 *
 * @internal
 */

final class Apishka_Templater_Extension_Staging extends Apishka_Templater_ExtensionAbstract
{
    private $functions = array();
    private $filters = array();
    private $visitors = array();
    private $tokenParsers = array();
    private $globals = array();
    private $tests = array();

    public function addFunction(Apishka_Templater_Function $function)
    {
        $this->functions[$function->getName()] = $function;
    }

    public function getFunctions()
    {
        return $this->functions;
    }

    public function addFilter(Apishka_Templater_Filter $filter)
    {
        $this->filters[$filter->getName()] = $filter;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function addNodeVisitor(Apishka_Templater_NodeVisitorInterface $visitor)
    {
        $this->visitors[] = $visitor;
    }

    public function getNodeVisitors()
    {
        return $this->visitors;
    }

    public function addTokenParser(Apishka_Templater_TokenParserInterface $parser)
    {
        $this->tokenParsers[] = $parser;
    }

    public function getTokenParsers()
    {
        return $this->tokenParsers;
    }

    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    public function getGlobals()
    {
        return $this->globals;
    }

    public function addTest(Apishka_Templater_Test $test)
    {
        $this->tests[$test->getName()] = $test;
    }

    public function getTests()
    {
        return $this->tests;
    }

    public function getName()
    {
        return 'staging';
    }
}
