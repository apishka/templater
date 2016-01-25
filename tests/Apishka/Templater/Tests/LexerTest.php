<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Templater_Tests_LexerTest extends PHPUnit_Framework_TestCase
{
    public function testNameLabelForTag()
    {
        $template = '{% § %}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);

        $stream->expect(Apishka_Templater_Token::BLOCK_START_TYPE);
        $this->assertSame('§', $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue());
    }

    public function testNameLabelForFunction()
    {
        $template = '{{ §() }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);

        $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
        $this->assertSame('§', $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue());
    }

    public function testBracketsNesting()
    {
        $template = '{{ {"a":{"b":"c"}} }}';

        $this->assertEquals(2, $this->countToken($template, Apishka_Templater_Token::PUNCTUATION_TYPE, '{'));
        $this->assertEquals(2, $this->countToken($template, Apishka_Templater_Token::PUNCTUATION_TYPE, '}'));
    }

    protected function countToken($template, $type, $value = null)
    {
        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);

        $count = 0;
        while (!$stream->isEOF()) {
            $token = $stream->next();
            if ($type === $token->getType()) {
                if (null === $value || $value === $token->getValue()) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    public function testLineDirective()
    {
        $template = "foo\n"
            . "bar\n"
            . "{% line 10 %}\n"
            . "{{\n"
            . "baz\n"
            . "}}\n";

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);

        // foo\nbar\n
        $this->assertSame(1, $stream->expect(Apishka_Templater_Token::TEXT_TYPE)->getLine());
        // \n (after {% line %})
        $this->assertSame(10, $stream->expect(Apishka_Templater_Token::TEXT_TYPE)->getLine());
        // {{
        $this->assertSame(11, $stream->expect(Apishka_Templater_Token::VAR_START_TYPE)->getLine());
        // baz
        $this->assertSame(12, $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getLine());
    }

    public function testLineDirectiveInline()
    {
        $template = "foo\n"
            . "bar{% line 10 %}{{\n"
            . "baz\n"
            . "}}\n";

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);

        // foo\nbar
        $this->assertSame(1, $stream->expect(Apishka_Templater_Token::TEXT_TYPE)->getLine());
        // {{
        $this->assertSame(10, $stream->expect(Apishka_Templater_Token::VAR_START_TYPE)->getLine());
        // baz
        $this->assertSame(11, $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getLine());
    }

    public function testLongComments()
    {
        $template = '{# ' . str_repeat('*', 100000) . ' #}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);

        // should not throw an exception
    }

    public function testLongVerbatim()
    {
        $template = '{% verbatim %}' . str_repeat('*', 100000) . '{% endverbatim %}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);

        // should not throw an exception
    }

    public function testLongVar()
    {
        $template = '{{ ' . str_repeat('x', 100000) . ' }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);

        // should not throw an exception
    }

    public function testLongBlock()
    {
        $template = '{% ' . str_repeat('x', 100000) . ' %}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);

        // should not throw an exception
    }

    public function testBigNumbers()
    {
        $template = '{{ 922337203685477580700 }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->next();
        $node = $stream->next();
        $this->assertEquals('922337203685477580700', $node->getValue());
    }

    public function testStringWithEscapedDelimiter()
    {
        $tests = array(
            "{{ 'foo \' bar' }}" => 'foo \' bar',
            '{{ "foo \" bar" }}' => 'foo " bar',
        );

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        foreach ($tests as $template => $expected) {
            $stream = $lexer->tokenize($template);
            $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
            $stream->expect(Apishka_Templater_Token::STRING_TYPE, $expected);
        }
    }

    public function testStringWithInterpolation()
    {
        $template = 'foo {{ "bar #{ baz + 1 }" }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->expect(Apishka_Templater_Token::TEXT_TYPE, 'foo ');
        $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'bar ');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_START_TYPE);
        $stream->expect(Apishka_Templater_Token::NAME_TYPE, 'baz');
        $stream->expect(Apishka_Templater_Token::OPERATOR_TYPE, '+');
        $stream->expect(Apishka_Templater_Token::NUMBER_TYPE, '1');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_END_TYPE);
        $stream->expect(Apishka_Templater_Token::VAR_END_TYPE);
    }

    public function testStringWithEscapedInterpolation()
    {
        $template = '{{ "bar \#{baz+1}" }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'bar #{baz+1}');
        $stream->expect(Apishka_Templater_Token::VAR_END_TYPE);
    }

    public function testStringWithHash()
    {
        $template = '{{ "bar # baz" }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'bar # baz');
        $stream->expect(Apishka_Templater_Token::VAR_END_TYPE);
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unclosed """
     */
    public function testStringWithUnterminatedInterpolation()
    {
        $template = '{{ "bar #{x" }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);
    }

    public function testStringWithNestedInterpolations()
    {
        $template = '{{ "bar #{ "foo#{bar}" }" }}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'bar ');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_START_TYPE);
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'foo');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_START_TYPE);
        $stream->expect(Apishka_Templater_Token::NAME_TYPE, 'bar');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_END_TYPE);
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_END_TYPE);
        $stream->expect(Apishka_Templater_Token::VAR_END_TYPE);
    }

    public function testStringWithNestedInterpolationsInBlock()
    {
        $template = '{% foo "bar #{ "foo#{bar}" }" %}';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->expect(Apishka_Templater_Token::BLOCK_START_TYPE);
        $stream->expect(Apishka_Templater_Token::NAME_TYPE, 'foo');
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'bar ');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_START_TYPE);
        $stream->expect(Apishka_Templater_Token::STRING_TYPE, 'foo');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_START_TYPE);
        $stream->expect(Apishka_Templater_Token::NAME_TYPE, 'bar');
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_END_TYPE);
        $stream->expect(Apishka_Templater_Token::INTERPOLATION_END_TYPE);
        $stream->expect(Apishka_Templater_Token::BLOCK_END_TYPE);
    }

    public function testOperatorEndingWithALetterAtTheEndOfALine()
    {
        $template = "{{ 1 and\n0}}";

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $stream = $lexer->tokenize($template);
        $stream->expect(Apishka_Templater_Token::VAR_START_TYPE);
        $stream->expect(Apishka_Templater_Token::NUMBER_TYPE, 1);
        $stream->expect(Apishka_Templater_Token::OPERATOR_TYPE, 'and');
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unclosed "variable" at line 3
     */
    public function testUnterminatedVariable()
    {
        $template = '

{{

bar


';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);
    }

    /**
     * @expectedException Apishka_Templater_Error_Syntax
     * @expectedExceptionMessage Unclosed "block" at line 3
     */
    public function testUnterminatedBlock()
    {
        $template = '

{%

bar


';

        $lexer = new Apishka_Templater_Lexer(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')));
        $lexer->tokenize($template);
    }
}
