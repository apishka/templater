<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Templater_Tests_ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "foo" tag. Did you mean "for" at line 1?
     */
    public function testUnknownTag()
    {
        $stream = new Apishka_Templater_TokenStream(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::NAME_TYPE, 'foo', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_END_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::EOF_TYPE, '', 1),
        ));
        $parser = new Apishka_Templater_Parser(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $parser->parse($stream);
    }

    /**
     * @expectedException        Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unknown "foobar" tag at line 1.
     */
    public function testUnknownTagWithoutSuggestions()
    {
        $stream = new Apishka_Templater_TokenStream(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::NAME_TYPE, 'foobar', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_END_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::EOF_TYPE, '', 1),
        ));
        $parser = new Apishka_Templater_Parser(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $parser->parse($stream);
    }

    /**
     * @dataProvider getFilterBodyNodesData
     */
    public function testFilterBodyNodes($input, $expected)
    {
        $parser = $this->getParser();
        $m = new ReflectionMethod($parser, 'filterBodyNodes');
        $m->setAccessible(true);

        $this->assertEquals($expected, $m->invoke($parser, $input));
    }

    public function getFilterBodyNodesData()
    {
        return array(
            array(
                Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Text::apishka('   ', 1))),
                Apishka_Templater_Node::apishka(array()),
            ),
            array(
                $input = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Set::apishka(false, Apishka_Templater_Node::apishka(), Apishka_Templater_Node::apishka(), 1))),
                $input,
            ),
            array(
                $input = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Set::apishka(true, Apishka_Templater_Node::apishka(), Apishka_Templater_Node::apishka(array(Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Text::apishka('foo', 1))))), 1))),
                $input,
            ),
        );
    }

    /**
     * @dataProvider getFilterBodyNodesDataThrowsException
     * @expectedException Apishka_Templater_Error_Syntax
     */
    public function testFilterBodyNodesThrowsException($input)
    {
        $parser = $this->getParser();

        $m = new ReflectionMethod($parser, 'filterBodyNodes');
        $m->setAccessible(true);

        $m->invoke($parser, $input);
    }

    public function getFilterBodyNodesDataThrowsException()
    {
        return array(
            array(Apishka_Templater_Node_Text::apishka('foo', 1)),
            array(Apishka_Templater_Node::apishka(array(Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Text::apishka('foo', 1)))))),
        );
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage A template that extends another one cannot have a body but a byte order mark (BOM) has been detected; it must be removed at line 1.
     */
    public function testFilterBodyNodesWithBOM()
    {
        $parser = $this->getParser();

        $m = new ReflectionMethod($parser, 'filterBodyNodes');
        $m->setAccessible(true);
        $m->invoke($parser, Apishka_Templater_Node_Text::apishka(chr(0xEF) . chr(0xBB) . chr(0xBF), 1));
    }

    public function testParseIsReentrant()
    {
        $twig = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'), array(
            'autoescape'    => false,
            'optimizations' => 0,
        ));
        $twig->addTokenParser(new TestTokenParser());

        $parser = new Apishka_Templater_Parser($twig);

        $parser->parse(new Apishka_Templater_TokenStream(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::NAME_TYPE, 'test', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_END_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::VAR_START_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::NAME_TYPE, 'foo', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::VAR_END_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::EOF_TYPE, '', 1),
        )));

        $this->assertNull($parser->getParent());
    }

    protected function getParser()
    {
        $parser = new Apishka_Templater_Parser(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $parser->setParent(Apishka_Templater_Node::apishka());
        $p = new ReflectionProperty($parser, 'stream');
        $p->setAccessible(true);
        $p->setValue($parser, $this->getMockBuilder('Apishka_Templater_TokenStream')->disableOriginalConstructor()->getMock());

        return $parser;
    }
}

class TestTokenParser extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
        // simulate the parsing of another template right in the middle of the parsing of the current template
        $this->parser->parse(new Apishka_Templater_TokenStream(array(
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_START_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::NAME_TYPE, 'extends', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::STRING_TYPE, 'base', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::BLOCK_END_TYPE, '', 1),
            new Apishka_Templater_Token(Apishka_Templater_Token::EOF_TYPE, '', 1),
        )));

        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node::apishka(array());
    }

    public function getTag()
    {
        return 'test';
    }
}
