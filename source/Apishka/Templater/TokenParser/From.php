<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Imports macros.
 *
 * <pre>
 *   {% from 'forms.html' import forms %}
 * </pre>
 */
class Apishka_Templater_TokenParser_From extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect('import');

        $targets = array();
        do {
            $name = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();

            $alias = $name;
            if ($stream->nextIf('as')) {
                $alias = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();
            }

            $targets[$name] = $alias;

            if (!$stream->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        } while (true);

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $node = Apishka_Templater_Node_Import::apishka($macro, Apishka_Templater_Node_Expression_AssignName::apishka($this->parser->getVarName(), $token->getLine()), $token->getLine(), $this->getTag());

        foreach ($targets as $name => $alias) {
            $this->parser->addImportedSymbol('function', $alias, 'macro_' . $name, $node->getNode('var'));
        }

        return $node;
    }

    public function getTag()
    {
        return 'from';
    }
}
