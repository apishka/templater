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
 * Defines a variable.
 *
 * <pre>
 *  {% set foo = 'foo' %}
 *
 *  {% set foo = [1, 2] %}
 *
 *  {% set foo = {'foo': 'bar'} %}
 *
 *  {% set foo = 'foo' ~ 'bar' %}
 *
 *  {% set foo, bar = 'foo', 'bar' %}
 *
 *  {% set foo %}Some content{% endset %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Set extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $names = $this->parser->getExpressionParser()->parseAssignmentExpression();

        $capture = false;
        if ($stream->nextIf(Apishka_Templater_Token::OPERATOR_TYPE, '=')) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();

            $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

            if (count($names) !== count($values)) {
                throw new Apishka_Templater_Error_Syntax('When using set, you must have the same number of variables and assignments.', $stream->getCurrent()->getLine(), $stream->getFilename());
            }
        } else {
            $capture = true;

            if (count($names) > 1) {
                throw new Apishka_Templater_Error_Syntax('When using set with a block, you cannot have a multi-target.', $stream->getCurrent()->getLine(), $stream->getFilename());
            }

            $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

            $values = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);
        }

        return Apishka_Templater_Node_Set::apishka($capture, $names, $values, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endset');
    }

    public function getTag()
    {
        return 'set';
    }
}
