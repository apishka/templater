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
 * Imports macros.
 *
 * <pre>
 *   {% import 'forms.html' as forms %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Import extends Apishka_Templater_TokenParser
{
    public function parse(Apishka_Templater_Token $token)
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect('as');
        $var = new Apishka_Templater_Node_Expression_AssignName($this->parser->getStream()->expect(Apishka_Templater_Token::NAME_TYPE)->getValue(), $token->getLine());
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $this->parser->addImportedSymbol('template', $var->getAttribute('name'));

        return new Apishka_Templater_Node_Import($macro, $var, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'import';
    }
}
