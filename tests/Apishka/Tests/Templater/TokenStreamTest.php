<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_TokenStreamTest extends PHPUnit_Framework_TestCase
{
    protected static $tokens;

    protected function setUp()
    {
        self::$tokens = array(
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 1, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 2, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 3, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 4, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 5, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 6, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::TEXT_TYPE, 7, 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::EOF_TYPE, 0, 1),
        );
    }

    public function testNext()
    {
        $stream = new Apishka_Templater_TokenStream(self::$tokens);
        $repr = array();
        while (!$stream->isEOF()) {
            $token = $stream->next();

            $repr[] = $token->getValue();
        }
        $this->assertEquals('1, 2, 3, 4, 5, 6, 7', implode(', ', $repr), '->next() advances the pointer and returns the current token');
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @expectedMessage   Unexpected end of template
     */
    public function testEndOfTemplateNext()
    {
        $stream = new Apishka_Templater_TokenStream(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, 1, 1),
        ));
        while (!$stream->isEOF()) {
            $stream->next();
        }
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @expectedMessage   Unexpected end of template
     */
    public function testEndOfTemplateLook()
    {
        $stream = new Apishka_Templater_TokenStream(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, 1, 1),
        ));
        while (!$stream->isEOF()) {
            $stream->look();
            $stream->next();
        }
    }
}
