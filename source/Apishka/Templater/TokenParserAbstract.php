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
 */

abstract class Apishka_Templater_TokenParserAbstract implements Apishka_Templater_TokenParserInterface
{
    /**
     * Traits
     */

    use Apishka\EasyExtend\Helper\ByClassNameTrait;

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
