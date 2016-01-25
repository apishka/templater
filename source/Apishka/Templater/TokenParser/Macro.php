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
 * Defines a macro.
 *
 * <pre>
 * {% macro input(name, value, type, size) %}
 *    <input type="{{ type|default('text') }}" name="{{ name }}" value="{{ value|e }}" size="{{ size|default(20) }}" />
 * {% endmacro %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Macro extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();

        $arguments = $this->parser->getExpressionParser()->parseArguments(true, true);

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);
        $this->parser->pushLocalScope();
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        if ($token = $stream->nextIf(Apishka_Templater_Token::NAME_TYPE)) {
            $value = $token->getValue();

            if ($value != $name) {
                throw new Apishka_Templater_Error_Syntax(sprintf('Expected endmacro for macro "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        }
        $this->parser->popLocalScope();
        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $this->parser->setMacro($name, Apishka_Templater_Node_Macro::apishka($name, Apishka_Templater_Node_Body::apishka(array($body)), $arguments, $lineno, $this->getTag()));
    }

    public function decideBlockEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endmacro');
    }

    public function getTag()
    {
        return 'macro';
    }
}
