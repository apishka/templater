<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extends a template by another one.
 *
 * <pre>
 *  {% extends "base.html" %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Extends extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        if (!$this->parser->isMainScope()) {
            throw new Apishka_Templater_Error_Syntax('Cannot extend from a block.', $token->getLine(), $this->parser->getFilename());
        }

        if (null !== $this->parser->getParent()) {
            throw new Apishka_Templater_Error_Syntax('Multiple extends tags are forbidden.', $token->getLine(), $this->parser->getFilename());
        }

        $parent = $this->parser->getExpressionParser()->parseExpression();

        $stream = $this->parser->getStream();

        $options = array();
        while (!$stream->test(Apishka_Templater_Token::BLOCK_END_TYPE)) {
            $token = $stream->expect(Apishka_Templater_Token::NAME_TYPE);

            $name = $token->getValue();
            $stream->expect(Apishka_Templater_Token::OPERATOR_TYPE, '=');

            $options[$name] = $this->parser->getExpressionParser()->parseExpression();
        }

        $parent->setAttribute('parent_options', $options);

        $this->parser->setParent($parent);

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);
    }

    public function getTag()
    {
        return 'extends';
    }
}
