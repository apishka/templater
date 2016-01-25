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
 * Base class for all token parsers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */

abstract class Apishka_Templater_TokenParser implements Apishka_Templater_TokenParserInterface
{
    /**
     * @var Apishka_Templater_Parser
     */
    protected $parser;

    /**
     * Sets the parser associated with this token parser.
     *
     * @param Apishka_Templater_Parser $parser A Apishka_Templater_Parser instance
     */
    public function setParser(Apishka_Templater_Parser $parser)
    {
        $this->parser = $parser;
    }
}
