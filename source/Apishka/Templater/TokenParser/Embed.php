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
 * Embeds a template.
 *
 * @easy-extend-base
 *
 */
class Apishka_Templater_TokenParser_Embed extends Apishka_Templater_TokenParser_Include
{
    public function parse(Apishka_Templater_Token $token)
    {
        $stream = $this->parser->getStream();

        $parent = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        // inject a fake parent to make the parent() function work
        $stream->injectTokens(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Apishka_Templater_Token(Apishka_Templater_Token::NAME_TYPE, 'extends', $token->getLine()),
            new Apishka_Templater_Token(Apishka_Templater_Token::STRING_TYPE, '__parent__', $token->getLine()),
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_END_TYPE, '', $token->getLine()),
        ));

        $module = $this->parser->parse($stream, array($this, 'decideBlockEnd'), true);

        // override the parent with the correct one
        $module->setNode('parent', $parent);

        $this->parser->embedTemplate($module);

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node_Embed::apishka($module->getAttribute('filename'), $module->getAttribute('index'), $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endembed');
    }

    public function getTag()
    {
        return 'embed';
    }
}
