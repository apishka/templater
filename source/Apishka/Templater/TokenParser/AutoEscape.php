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
 * Marks a section of a template to be escaped or not.
 */
class Apishka_Templater_TokenParser_AutoEscape extends Apishka_Templater_TokenParser
{
    public function parse(Apishka_Templater_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        if ($stream->test(Apishka_Templater_Token::BLOCK_END_TYPE)) {
            $value = 'html';
        } else {
            $expr = $this->parser->getExpressionParser()->parseExpression();
            if (!$expr instanceof Apishka_Templater_Node_Expression_Constant) {
                throw new Apishka_Templater_Error_Syntax('An escaping strategy must be a string or false.', $stream->getCurrent()->getLine(), $stream->getFilename());
            }
            $value = $expr->getAttribute('value');
        }

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node_AutoEscape::apishka($value, $body, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endautoescape');
    }

    public function getTag()
    {
        return 'autoescape';
    }
}
