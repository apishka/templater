<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Marks a section of a template as being reusable.
 *
 * <pre>
 *  {% block head %}
 *    <link rel="stylesheet" href="style.css" />
 *    <title>{% block title %}{% endblock %} - My Webpage</title>
 *  {% endblock %}
 * </pre>
 */
class Apishka_Templater_TokenParser_Block extends Apishka_Templater_TokenParser
{
    public function parse(Apishka_Templater_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();
        if ($this->parser->hasBlock($name)) {
            throw new Apishka_Templater_Error_Syntax(sprintf("The block '%s' has already been defined line %d.", $name, $this->parser->getBlock($name)->getLine()), $stream->getCurrent()->getLine(), $stream->getFilename());
        }
        $this->parser->setBlock($name, $block = Apishka_Templater_Node_Block::apishka($name, Apishka_Templater_Node::apishka(array()), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        if ($stream->nextIf(Apishka_Templater_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($token = $stream->nextIf(Apishka_Templater_Token::NAME_TYPE)) {
                $value = $token->getValue();

                if ($value != $name) {
                    throw new Apishka_Templater_Error_Syntax(sprintf('Expected endblock for block "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getFilename());
                }
            }
        } else {
            $body = Apishka_Templater_Node::apishka(array(
                Apishka_Templater_Node_Print::apishka($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ));
        }
        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return Apishka_Templater_Node_BlockReference::apishka($name, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endblock');
    }

    public function getTag()
    {
        return 'block';
    }
}
