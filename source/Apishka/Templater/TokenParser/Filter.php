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
 * Filters a section of a template by applying filters.
 *
 * <pre>
 * {% filter upper %}
 *  This text becomes uppercase
 * {% endfilter %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Filter extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        $name = $this->parser->getVarName();
        $ref = Apishka_Templater_Node_Expression_BlockReference::apishka(
            Apishka_Templater_Node_Expression_Constant::apishka(
                $name,
                $token->getLine()
            ),
            Apishka_Templater_Node::apishka(),
            true,
            $token->getLine(),
            $this->getTag()
        );

        $filter = $this->parser->getExpressionParser()->parseFilterExpressionRaw($ref, $this->getTag());
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $block = Apishka_Templater_Node_Block::apishka($name, $body, $token->getLine());
        $this->parser->setBlock($name, $block);

        return Apishka_Templater_Node_Print::apishka($filter, $token->getLine(), $this->getTag());
    }

    public function decideBlockEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endfilter');
    }

    public function getTag()
    {
        return 'filter';
    }
}
