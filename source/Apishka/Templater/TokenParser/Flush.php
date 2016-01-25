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
 * Flushes the output to the client.
 *
 * @see flush()
 */
class Apishka_Templater_TokenParser_Flush extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node_Flush::apishka($token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'flush';
    }
}
