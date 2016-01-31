<?php

/**
 * Apishka templater token parser translate
 *
 * @uses Apishka_Templater_TokenParserAbstract
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_TokenParser_Translate extends Apishka_Templater_TokenParserAbstract
{
    /**
     * Parse
     *
     * @param Apishka_Templater_Token $token
     * @return Apishka_Templater_Node_Translation
     */

    public function parse(Apishka_Templater_Token $token)
    {
        list($params, $variables) = $this->parseArguments();
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideTranslateEnd'), true);
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node_Translate::apishka(
            $body,
            $params,
            $variables,
            $token->getLine(),
            $this->getTag()
        );
    }

    /**
     * Parse arguments
     *
     * @return array
     */

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $variables = null;
        $params = array();
        while (!$stream->test(Apishka_Templater_Token::BLOCK_END_TYPE))
        {
            if ($stream->nextIf(Apishka_Templater_Token::NAME_TYPE, 'with'))
            {
                $variables = $this->parser->getExpressionParser()->parseExpression();
                continue;
            }

            $token = $stream->expect(Apishka_Templater_Token::NAME_TYPE);

            $param_name = $token->getValue();

            $stream->expect(Apishka_Templater_Token::OPERATOR_TYPE, '=');

            $params[] = array(
                $param_name,
                $this->parser->getExpressionParser()->parseExpression()
            );
        }

        return array(
            $params,
            $variables
        );
    }

    /**
     * Decide translate end
     *
     * @param Apishka_Templater_Token $token
     * @return bool
     */

    public function decideTranslateEnd(Apishka_Templater_Token $token)
    {
        return $token->test('endtranslate');
    }

    /**
     * Get tag
     *
     * @return string
     */

    public function getTag()
    {
        return 'translate';
    }
}
