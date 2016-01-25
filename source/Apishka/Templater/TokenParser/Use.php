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
 * Imports blocks defined in another template into the current template.
 *
 * <pre>
 * {% extends "base.html" %}
 *
 * {% use "blocks.html" %}
 *
 * {% block title %}{% endblock %}
 * {% block content %}{% endblock %}
 * </pre>
 *
 * @see http://www.twig-project.org/doc/templates.html#horizontal-reuse for details.
 */
class Apishka_Templater_TokenParser_Use extends Apishka_Templater_TokenParser
{
    public function parse(Apishka_Templater_Token $token)
    {
        $template = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();

        if (!$template instanceof Apishka_Templater_Node_Expression_Constant) {
            throw new Apishka_Templater_Error_Syntax('The template references in a "use" statement must be a string.', $stream->getCurrent()->getLine(), $stream->getFilename());
        }

        $targets = array();
        if ($stream->nextIf('with')) {
            do {
                $name = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();

                $alias = $name;
                if ($stream->nextIf('as')) {
                    $alias = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();
                }

                $targets[$name] = Apishka_Templater_Node_Expression_Constant::apishka($alias, -1);

                if (!$stream->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ',')) {
                    break;
                }
            } while (true);
        }

        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $this->parser->addTrait(Apishka_Templater_Node::apishka(array('template' => $template, 'targets' => Apishka_Templater_Node::apishka($targets))));
    }

    public function getTag()
    {
        return 'use';
    }
}
