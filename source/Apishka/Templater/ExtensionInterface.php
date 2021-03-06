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
 * Interface implemented by extension classes.
 */
interface Apishka_Templater_ExtensionInterface
{
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return Apishka_Templater_TokenParserInterface[]
     */
    public function getTokenParsers();

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return Apishka_Templater_NodeVisitorInterface[] An array of Apishka_Templater_NodeVisitorInterface instances
     */
    public function getNodeVisitors();

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return Apishka_Templater_SimpleFilter[]
     */
    public function getFilters();

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return Apishka_Templater_SimpleTest[]
     */
    public function getTests();

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return Apishka_Templater_SimpleFunction[]
     */
    public function getFunctions();

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array An array of operators
     */
    public function getOperators();

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName();
}
