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
 * Includes a template.
 *
 * <pre>
 *   {% include 'header.html' %}
 *     Body
 *   {% include 'footer.html' %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Include extends Apishka_Templater_TokenParser
{
    public function parse(Apishka_Templater_Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        return Apishka_Templater_Node_Include::apishka($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $ignoreMissing = false;
        if ($stream->nextIf(Apishka_Templater_Token::NAME_TYPE, 'ignore')) {
            $stream->expect(Apishka_Templater_Token::NAME_TYPE, 'missing');

            $ignoreMissing = true;
        }

        $variables = null;
        if ($stream->nextIf(Apishka_Templater_Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $only = false;
        if ($stream->nextIf(Apishka_Templater_Token::NAME_TYPE, 'only')) {
            $only = true;
        }

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return array($variables, $only, $ignoreMissing);
    }

    public function getTag()
    {
        return 'include';
    }
}
