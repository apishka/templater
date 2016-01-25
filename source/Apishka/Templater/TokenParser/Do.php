<?php

/*
 * This file is part of Twig.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Evaluates an expression, discarding the returned value.
 */
class Apishka_Templater_TokenParser_Do extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node_Do::apishka($expr, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'do';
    }
}
