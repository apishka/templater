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
 * Interface implemented by token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Apishka_Templater_TokenParserInterface
{
    /**
     * Sets the parser associated with this token parser.
     *
     * @param Apishka_Templater_Parser $parser A Apishka_Templater_Parser instance
     */
    public function setParser(Apishka_Templater_Parser $parser);

    /**
     * Parses a token and returns a node.
     *
     * @param Apishka_Templater_Token $token A Apishka_Templater_Token instance
     *
     * @throws Apishka_Templater_Error_Syntax
     *
     * @return Apishka_Templater_Node A Apishka_Templater_Node instance
     */
    public function parse(Apishka_Templater_Token $token);

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag();
}
