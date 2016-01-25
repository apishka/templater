<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// This function is defined to check that escaping strategies
// like html works even if a function with the same name is defined.
function html()
{
    return 'foo';
}

class Apishka_Templater_Tests_IntegrationTest extends Apishka_Templater_Test_IntegrationTestCaseAbstract
{
    public function getExtensions()
    {
        $policy = new Apishka_Templater_Sandbox_SecurityPolicy(array(), array(), array(), array(), array());

        return array(
            new Apishka_Templater_Extension_Debug(),
            new Apishka_Templater_Extension_Sandbox($policy, false),
            new Apishka_Templater_Extension_StringLoader(),
            new TwigTestExtension(),
        );
    }

    public function getFixturesDir()
    {
        return __DIR__ . '/Fixtures/';
    }
}

function test_foo($value = 'foo')
{
    return $value;
}

class TwigTestFoo implements Iterator
{
    const BAR_NAME = 'bar';

    public $position = 0;
    public $array = array(1, 2);

    public function bar($param1 = null, $param2 = null)
    {
        return 'bar' . ($param1 ? '_' . $param1 : '') . ($param2 ? '-' . $param2 : '');
    }

    public function getFoo()
    {
        return 'foo';
    }

    public function getSelf()
    {
        return $this;
    }

    public function is()
    {
        return 'is';
    }

    public function in()
    {
        return 'in';
    }

    public function not()
    {
        return 'not';
    }

    public function strToLower($value)
    {
        return strtolower($value);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->array[$this->position];
    }

    public function key()
    {
        return 'a';
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->array[$this->position]);
    }
}

class TwigTestTokenParser_§ extends Apishka_Templater_TokenParser
{
    public function parse(Apishka_Templater_Token $token)
    {
        $this->parser->getStream()->expect(Apishka_Templater_Token::BLOCK_END_TYPE);

        return Apishka_Templater_Node_Print::apishka(Apishka_Templater_Node_Expression_Constant::apishka('§', -1), -1);
    }

    public function getTag()
    {
        return '§';
    }
}

class TwigTestExtension extends Apishka_Templater_ExtensionAbstract
{
    public function getTokenParsers()
    {
        return array(
            new TwigTestTokenParser_§(),
        );
    }

    public function getFilters()
    {
        return array(
            new Apishka_Templater_Filter('§', array($this, '§Filter')),
            new Apishka_Templater_Filter('escape_and_nl2br', array($this, 'escape_and_nl2br'), array('needs_environment' => true, 'is_safe' => array('html'))),
            new Apishka_Templater_Filter('nl2br', array($this, 'nl2br'), array('pre_escape' => 'html', 'is_safe' => array('html'))),
            new Apishka_Templater_Filter('escape_something', array($this, 'escape_something'), array('is_safe' => array('something'))),
            new Apishka_Templater_Filter('preserves_safety', array($this, 'preserves_safety'), array('preserves_safety' => array('html'))),
            new Apishka_Templater_Filter('*_path', array($this, 'dynamic_path')),
            new Apishka_Templater_Filter('*_foo_*_bar', array($this, 'dynamic_foo')),
            new Apishka_Templater_Filter('anon_foo', function ($name) { return '*' . $name . '*'; }),
        );
    }

    public function getFunctions()
    {
        return array(
            new Apishka_Templater_Function('§', array($this, '§Function')),
            new Apishka_Templater_Function('safe_br', array($this, 'br'), array('is_safe' => array('html'))),
            new Apishka_Templater_Function('unsafe_br', array($this, 'br')),
            new Apishka_Templater_Function('*_path', array($this, 'dynamic_path')),
            new Apishka_Templater_Function('*_foo_*_bar', array($this, 'dynamic_foo')),
            new Apishka_Templater_Function('anon_foo', function ($name) { return '*' . $name . '*'; }),
        );
    }

    public function getTests()
    {
        return array(
            new Apishka_Templater_Test('multi word', array($this, 'is_multi_word')),
        );
    }

    public function §Filter($value)
    {
        return "§{$value}§";
    }

    public function §Function($value)
    {
        return "§{$value}§";
    }

    /**
     * nl2br which also escapes, for testing escaper filters.
     */
    public function escape_and_nl2br($env, $value, $sep = '<br />')
    {
        return $this->nl2br(twig_escape_filter($env, $value, 'html'), $sep);
    }

    /**
     * nl2br only, for testing filters with pre_escape.
     */
    public function nl2br($value, $sep = '<br />')
    {
        // not secure if $value contains html tags (not only entities)
        // don't use
        return str_replace("\n", "$sep\n", $value);
    }

    public function dynamic_path($element, $item)
    {
        return $element . '/' . $item;
    }

    public function dynamic_foo($foo, $bar, $item)
    {
        return $foo . '/' . $bar . '/' . $item;
    }

    public function escape_something($value)
    {
        return strtoupper($value);
    }

    public function preserves_safety($value)
    {
        return strtoupper($value);
    }

    public function br()
    {
        return '<br />';
    }

    public function is_multi_word($value)
    {
        return false !== strpos($value, ' ');
    }

    public function getName()
    {
        return 'integration_test';
    }
}
