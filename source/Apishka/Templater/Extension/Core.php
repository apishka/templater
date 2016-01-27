<?php

// use 0 as hhvm does not support several flags yet
if (!defined('ENT_SUBSTITUTE'))
    define('ENT_SUBSTITUTE', 0);

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka templater extension core
 *
 * @uses Apishka_Templater_ExtensionAbstract
 *
 * @author Evgeny Reykh <evgeny@reykh.com>
 */

class Apishka_Templater_Extension_Core extends Apishka_Templater_ExtensionAbstract
{
    /**
     * Date formats
     *
     * @var array
     */

    private $_date_formats = array('F j, Y H:i', '%d days');

    /**
     * Number format
     *
     * @var array
     */

    private $_number_format = array(0, '.', ',');

    /**
     * Timezone
     *
     * @var string
     */

    private $_timezone = null;

    /**
     * Escapers
     *
     * @var array
     */

    private $_escapers = array();

    /**
     * Defines a new escaper to be used via the escape filter.
     *
     * @param string   $strategy The strategy name that should be used as a strategy in the escape call
     * @param Callable $callable A valid PHP callable
     */

    public function setEscaper($strategy, callable $callable)
    {
        $this->_escapers[$strategy] = $callable;
    }

    /**
     * Gets all defined escapers.
     *
     * @return callable[] An array of escapers
     */

    public function getEscapers()
    {
        return $this->_escapers;
    }

    /**
     * Sets the default format to be used by the date filter.
     *
     * @param string $format               The default date format string
     * @param string $date_interval_format The default date interval format string
     */

    public function setDateFormat($format = null, $date_interval_format = null)
    {
        if (null !== $format)
            $this->_date_formats[0] = $format;

        if (null !== $date_interval_format)
            $this->_date_formats[1] = $date_interval_format;
    }

    /**
     * Gets the default format to be used by the date filter.
     *
     * @return array The default date format string and the default date interval format string
     */

    public function getDateFormat()
    {
        return $this->_date_formats;
    }

    /**
     * Sets the default timezone to be used by the date filter.
     *
     * @param DateTimeZone|string $timezone The default timezone string or a DateTimeZone object
     */

    public function setTimezone($timezone)
    {
        $this->_timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
    }

    /**
     * Gets the default timezone to be used by the date filter.
     *
     * @return DateTimeZone The default timezone currently in use
     */

    public function getTimezone()
    {
        if (null === $this->_timezone)
            $this->_timezone = new DateTimeZone(date_default_timezone_get());

        return $this->_timezone;
    }

    /**
     * Sets the default format to be used by the number_format filter.
     *
     * @param int    $decimal      The number of decimal places to use.
     * @param string $decimalPoint The character(s) to use for the decimal point.
     * @param string $thousandSep  The character(s) to use for the thousands separator.
     */

    public function setNumberFormat($decimal, $decimalPoint, $thousandSep)
    {
        $this->_number_format = array($decimal, $decimalPoint, $thousandSep);
    }

    /**
     * Get the default format used by the number_format filter.
     *
     * @return array The arguments for number_format()
     */

    public function getNumberFormat()
    {
        return $this->_number_format;
    }

    /**
     * Get token parsers
     *
     * @return array
     */

    public function getTokenParsers()
    {
        return array(
            new Apishka_Templater_TokenParser_For(),
            new Apishka_Templater_TokenParser_If(),
            new Apishka_Templater_TokenParser_Extends(),
            new Apishka_Templater_TokenParser_Include(),
            new Apishka_Templater_TokenParser_Block(),
            new Apishka_Templater_TokenParser_Use(),
            new Apishka_Templater_TokenParser_Filter(),
            new Apishka_Templater_TokenParser_Macro(),
            new Apishka_Templater_TokenParser_Import(),
            new Apishka_Templater_TokenParser_From(),
            new Apishka_Templater_TokenParser_Set(),
            new Apishka_Templater_TokenParser_Spaceless(),
            new Apishka_Templater_TokenParser_Flush(),
            new Apishka_Templater_TokenParser_Do(),
            new Apishka_Templater_TokenParser_Embed(),
        );
    }

    /**
     * Get filters
     *
     * @return array
     */

    public function getFilters()
    {
        return array(
            // formatting filters
            new Apishka_Templater_Filter('date', 'twig_date_format_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('date_modify', 'twig_date_modify_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('format', 'sprintf'),
            new Apishka_Templater_Filter('replace', 'twig_replace_filter'),
            new Apishka_Templater_Filter('number_format', 'twig_number_format_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('abs', 'abs'),
            new Apishka_Templater_Filter('round', 'twig_round'),

            // encoding
            new Apishka_Templater_Filter('url_encode', 'twig_urlencode_filter'),
            new Apishka_Templater_Filter('json_encode', 'json_encode'),
            new Apishka_Templater_Filter('convert_encoding', 'twig_convert_encoding'),

            // string filters
            new Apishka_Templater_Filter('title', 'twig_title_string_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('capitalize', 'twig_capitalize_string_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('upper', 'twig_upper_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('lower', 'twig_lower_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('striptags', 'strip_tags'),
            new Apishka_Templater_Filter('trim', 'trim'),
            new Apishka_Templater_Filter('nl2br', 'nl2br', array('pre_escape' => 'html', 'is_safe' => array('html'))),

            // array helpers
            new Apishka_Templater_Filter('join', 'twig_join_filter'),
            new Apishka_Templater_Filter('split', 'twig_split_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('sort', 'twig_sort_filter'),
            new Apishka_Templater_Filter('merge', 'twig_array_merge'),
            new Apishka_Templater_Filter('batch', 'twig_array_batch'),

            // string/array filters
            new Apishka_Templater_Filter('reverse', 'twig_reverse_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('length', 'twig_length_filter', array('needs_environment' => true)),
            new Apishka_Templater_Filter('slice', 'twig_slice', array('needs_environment' => true)),
            new Apishka_Templater_Filter('first', 'twig_first', array('needs_environment' => true)),
            new Apishka_Templater_Filter('last', 'twig_last', array('needs_environment' => true)),

            // iteration and runtime
            new Apishka_Templater_Filter('default', '_twig_default_filter', array('node_class' => 'Apishka_Templater_Node_Expression_Filter_Default')),
            new Apishka_Templater_Filter('keys', 'twig_get_array_keys_filter'),

            // escaping
            new Apishka_Templater_Filter('escape', 'twig_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe')),
            new Apishka_Templater_Filter('e', 'twig_escape_filter', array('needs_environment' => true, 'is_safe_callback' => 'twig_escape_filter_is_safe')),
        );
    }

    /**
     * Get functions
     *
     * @return array
     */

    public function getFunctions()
    {
        return array(
            new Apishka_Templater_Function('max', 'max'),
            new Apishka_Templater_Function('min', 'min'),
            new Apishka_Templater_Function('range', 'range'),
            new Apishka_Templater_Function('constant', 'twig_constant'),
            new Apishka_Templater_Function('cycle', 'twig_cycle'),
            new Apishka_Templater_Function('random', 'twig_random', array('needs_environment' => true)),
            new Apishka_Templater_Function('date', 'twig_date_converter', array('needs_environment' => true)),
            new Apishka_Templater_Function('include', 'twig_include', array('needs_environment' => true, 'needs_context' => true, 'is_safe' => array('all'))),
            new Apishka_Templater_Function('source', 'twig_source', array('needs_environment' => true, 'is_safe' => array('all'))),
        );
    }

    /**
     * Get tests
     *
     * @return array
     */

    public function getTests()
    {
        return array(
            new Apishka_Templater_Test('even', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Even')),
            new Apishka_Templater_Test('odd', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Odd')),
            new Apishka_Templater_Test('defined', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Defined')),
            new Apishka_Templater_Test('same as', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Sameas')),
            new Apishka_Templater_Test('none', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Null')),
            new Apishka_Templater_Test('null', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Null')),
            new Apishka_Templater_Test('divisible by', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Divisibleby')),
            new Apishka_Templater_Test('constant', null, array('node_class' => 'Apishka_Templater_Node_Expression_Test_Constant')),
            new Apishka_Templater_Test('empty', 'twig_test_empty'),
            new Apishka_Templater_Test('iterable', 'twig_test_iterable'),
        );
    }

    /**
     * Get operators
     *
     * @return array
     */

    public function getOperators()
    {
        return array(
            array(
                'not' => array('precedence' => 50, 'class' => 'Apishka_Templater_Node_Expression_Unary_Not'),
                '-'   => array('precedence' => 500, 'class' => 'Apishka_Templater_Node_Expression_Unary_Neg'),
                '+'   => array('precedence' => 500, 'class' => 'Apishka_Templater_Node_Expression_Unary_Pos'),
            ),
            array(
                'or'          => array('precedence' => 10, 'class' => 'Apishka_Templater_Node_Expression_Binary_Or', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'and'         => array('precedence' => 15, 'class' => 'Apishka_Templater_Node_Expression_Binary_And', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'b-or'        => array('precedence' => 16, 'class' => 'Apishka_Templater_Node_Expression_Binary_BitwiseOr', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'b-xor'       => array('precedence' => 17, 'class' => 'Apishka_Templater_Node_Expression_Binary_BitwiseXor', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'b-and'       => array('precedence' => 18, 'class' => 'Apishka_Templater_Node_Expression_Binary_BitwiseAnd', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '=='          => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_Equal', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '!='          => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_NotEqual', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '<'           => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_Less', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '>'           => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_Greater', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '>='          => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_GreaterEqual', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '<='          => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_LessEqual', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'not in'      => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_NotIn', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'in'          => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_In', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'matches'     => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_Matches', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'starts with' => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_StartsWith', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'ends with'   => array('precedence' => 20, 'class' => 'Apishka_Templater_Node_Expression_Binary_EndsWith', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '..'          => array('precedence' => 25, 'class' => 'Apishka_Templater_Node_Expression_Binary_Range', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '+'           => array('precedence' => 30, 'class' => 'Apishka_Templater_Node_Expression_Binary_Add', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '-'           => array('precedence' => 30, 'class' => 'Apishka_Templater_Node_Expression_Binary_Sub', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '~'           => array('precedence' => 40, 'class' => 'Apishka_Templater_Node_Expression_Binary_Concat', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '*'           => array('precedence' => 60, 'class' => 'Apishka_Templater_Node_Expression_Binary_Mul', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '/'           => array('precedence' => 60, 'class' => 'Apishka_Templater_Node_Expression_Binary_Div', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '//'          => array('precedence' => 60, 'class' => 'Apishka_Templater_Node_Expression_Binary_FloorDiv', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '%'           => array('precedence' => 60, 'class' => 'Apishka_Templater_Node_Expression_Binary_Mod', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'is'          => array('precedence' => 100, 'callable' => array($this, 'parseTestExpression'), 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                'is not'      => array('precedence' => 100, 'callable' => array($this, 'parseNotTestExpression'), 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_LEFT),
                '**'          => array('precedence' => 200, 'class' => 'Apishka_Templater_Node_Expression_Binary_Power', 'associativity' => Apishka_Templater_ExpressionParser::OPERATOR_RIGHT),
            ),
        );
    }

    /**
     * Parse not test expression
     *
     * @param Apishka_Templater_Parser       $parser
     * @param Apishka_Templater_NodeAbstract $node
     *
     * @return Apishka_Templater_Node_Expression_Unary_Not
     */

    public function parseNotTestExpression(Apishka_Templater_Parser $parser, Apishka_Templater_NodeAbstract $node)
    {
        return Apishka_Templater_Node_Expression_Unary_Not::apishka(
            $this->parseTestExpression($parser, $node),
            $parser->getCurrentToken()->getLine()
        );
    }

    /**
     * Parse test expression
     *
     * @param Apishka_Templater_Parser       $parser
     * @param Apishka_Templater_NodeAbstract $node
     *
     * @return Apishka_Templater_NodeAbstract
     */

    public function parseTestExpression(Apishka_Templater_Parser $parser, Apishka_Templater_NodeAbstract $node)
    {
        $stream = $parser->getStream();
        $test = $this->getTest($parser, $node->getLine());
        $class = $test->getNodeClass();
        $arguments = null;

        if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '('))
            $arguments = $parser->getExpressionParser()->parseArguments(true);

        return new $class($node, $test->getName(), $arguments, $parser->getCurrentToken()->getLine());
    }

    /**
     * Get test
     *
     * @param Apishka_Templater_Parser $parser
     * @param mixed                    $line
     *
     * @return mixed
     */

    private function getTest(Apishka_Templater_Parser $parser, $line)
    {
        $stream = $parser->getStream();
        $name = $stream->expect(Apishka_Templater_Token::NAME_TYPE)->getValue();
        $env = $parser->getEnvironment();

        if ($test = $env->getTest($name))
            return $test;

        if ($stream->test(Apishka_Templater_Token::NAME_TYPE))
        {
            // try 2-words tests
            $name = $name . ' ' . $parser->getCurrentToken()->getValue();

            if ($test = $env->getTest($name))
            {
                $parser->getStream()->next();

                return $test;
            }
        }

        $e = new Apishka_Templater_Error_Syntax(sprintf('Unknown "%s" test.', $name), $line, $parser->getFilename());
        $e->addSuggestions($name, array_keys($env->getTests()));

        throw $e;
    }

    /**
     * Get name
     *
     * @return string
     */

    public function getName()
    {
        return 'core';
    }
}

/**
 * Cycles over a value.
 *
 * @param ArrayAccess|array $values   An array or an ArrayAccess instance
 * @param int               $position The cycle position
 *
 * @return string The next value in the cycle
 */
function twig_cycle($values, $position)
{
    if (!is_array($values) && !$values instanceof ArrayAccess) {
        return $values;
    }

    return $values[$position % count($values)];
}

/**
 * Returns a random value depending on the supplied parameter type:
 * - a random item from a Traversable or array
 * - a random character from a string
 * - a random integer between 0 and the integer parameter.
 *
 * @param Apishka_Templater_Environment $env    A Apishka_Templater_Environment instance
 * @param Traversable|array|int|string  $values The values to pick a random item from
 *
 * @throws Apishka_Templater_Error_Runtime When $values is an empty array (does not apply to an empty string which is returned as is).
 *
 * @return mixed A random value from the given sequence
 */
function twig_random(Apishka_Templater_Environment $env, $values = null)
{
    if (null === $values) {
        return mt_rand();
    }

    if (is_int($values) || is_float($values)) {
        return $values < 0 ? mt_rand($values, 0) : mt_rand(0, $values);
    }

    if ($values instanceof Traversable) {
        $values = iterator_to_array($values);
    } elseif (is_string($values)) {
        if ('' === $values) {
            return '';
        }

        $charset = $env->getCharset();

        if ('UTF-8' != $charset) {
            $values = iconv($charset, 'UTF-8', $values);
        }

        // unicode version of str_split()
        // split at all positions, but not after the start and not before the end
        $values = preg_split('/(?<!^)(?!$)/u', $values);

        if ('UTF-8' != $charset) {
            foreach ($values as $i => $value) {
                $values[$i] = iconv('UTF-8', $charset, $value);
            }
        }
    }

    if (!is_array($values)) {
        return $values;
    }

    if (0 === count($values)) {
        throw new Apishka_Templater_Error_Runtime('The random function cannot pick from an empty array.');
    }

    return $values[array_rand($values, 1)];
}

/**
 * Converts a date to the given format.
 *
 * <pre>
 *   {{ post.published_at|date("m/d/Y") }}
 * </pre>
 *
 * @param Apishka_Templater_Environment         $env      A Apishka_Templater_Environment instance
 * @param DateTimeInterface|DateInterval|string $date     A date
 * @param string|null                           $format   The target format, null to use the default
 * @param DateTimeZone|string|null|false        $timezone The target timezone, null to use the default, false to leave unchanged
 *
 * @return string The formatted date
 */
function twig_date_format_filter(Apishka_Templater_Environment $env, $date, $format = null, $timezone = null)
{
    if (null === $format) {
        $formats = $env->getExtension('core')->getDateFormat();
        $format = $date instanceof DateInterval ? $formats[1] : $formats[0];
    }

    if ($date instanceof DateInterval) {
        return $date->format($format);
    }

    return twig_date_converter($env, $date, $timezone)->format($format);
}

/**
 * Returns a new date object modified.
 *
 * <pre>
 *   {{ post.published_at|date_modify("-1day")|date("m/d/Y") }}
 * </pre>
 *
 * @param Apishka_Templater_Environment $env      A Apishka_Templater_Environment instance
 * @param DateTimeInterface|string      $date     A date
 * @param string                        $modifier A modifier string
 *
 * @return DateTimeInterface A new date object
 */
function twig_date_modify_filter(Apishka_Templater_Environment $env, $date, $modifier)
{
    $date = twig_date_converter($env, $date, false);

    return $date->modify($modifier);
}

/**
 * Converts an input to a DateTime instance.
 *
 * <pre>
 *    {% if date(user.created_at) < date('+2days') %}
 *      {# do something #}
 *    {% endif %}
 * </pre>
 *
 * @param Apishka_Templater_Environment  $env      A Apishka_Templater_Environment instance
 * @param DateTimeInterface|string|null  $date     A date or null to use the current time
 * @param DateTimeZone|string|null|false $timezone The target timezone, null to use the default, false to leave unchanged
 *
 * @return DateTime A DateTime instance
 */
function twig_date_converter(Apishka_Templater_Environment $env, $date = null, $timezone = null)
{
    // determine the timezone
    if (false !== $timezone) {
        if (null === $timezone) {
            $timezone = $env->getExtension('core')->getTimezone();
        } elseif (!$timezone instanceof DateTimeZone) {
            $timezone = new DateTimeZone($timezone);
        }
    }

    // immutable dates
    if ($date instanceof DateTimeImmutable) {
        return false !== $timezone ? $date->setTimezone($timezone) : $date;
    }

    if ($date instanceof DateTime || $date instanceof DateTimeInterface) {
        $date = clone $date;
        if (false !== $timezone) {
            $date->setTimezone($timezone);
        }

        return $date;
    }

    if (null === $date || 'now' === $date) {
        return new DateTime($date, false !== $timezone ? $timezone : $env->getExtension('core')->getTimezone());
    }

    $asString = (string) $date;
    if (ctype_digit($asString) || (!empty($asString) && '-' === $asString[0] && ctype_digit(substr($asString, 1)))) {
        $date = new DateTime('@' . $date);
    } else {
        $date = new DateTime($date, $env->getExtension('core')->getTimezone());
    }

    if (false !== $timezone) {
        $date->setTimezone($timezone);
    }

    return $date;
}

/**
 * Replaces strings within a string.
 *
 * @param string            $str  String to replace in
 * @param array|Traversable $from Replace values
 *
 * @return string
 */
function twig_replace_filter($str, $from)
{
    if ($from instanceof Traversable) {
        $from = iterator_to_array($from);
    } elseif (!is_array($from)) {
        throw new Apishka_Templater_Error_Runtime(sprintf('The "replace" filter expects an array or "Traversable" as replace values, got "%s".', is_object($from) ? get_class($from) : gettype($from)));
    }

    return strtr($str, $from);
}

/**
 * Rounds a number.
 *
 * @param int|float $value     The value to round
 * @param int|float $precision The rounding precision
 * @param string    $method    The method to use for rounding
 *
 * @return int|float The rounded number
 */
function twig_round($value, $precision = 0, $method = 'common')
{
    if ('common' == $method) {
        return round($value, $precision);
    }

    if ('ceil' != $method && 'floor' != $method) {
        throw new Apishka_Templater_Error_Runtime('The round filter only supports the "common", "ceil", and "floor" methods.');
    }

    return $method($value * pow(10, $precision)) / pow(10, $precision);
}

/**
 * Number format filter.
 *
 * All of the formatting options can be left null, in that case the defaults will
 * be used.  Supplying any of the parameters will override the defaults set in the
 * environment object.
 *
 * @param Apishka_Templater_Environment $env          A Apishka_Templater_Environment instance
 * @param mixed                         $number       A float/int/string of the number to format
 * @param int                           $decimal      The number of decimal points to display.
 * @param string                        $decimalPoint The character(s) to use for the decimal point.
 * @param string                        $thousandSep  The character(s) to use for the thousands separator.
 *
 * @return string The formatted number
 */
function twig_number_format_filter(Apishka_Templater_Environment $env, $number, $decimal = null, $decimalPoint = null, $thousandSep = null)
{
    $defaults = $env->getExtension('core')->getNumberFormat();
    if (null === $decimal) {
        $decimal = $defaults[0];
    }

    if (null === $decimalPoint) {
        $decimalPoint = $defaults[1];
    }

    if (null === $thousandSep) {
        $thousandSep = $defaults[2];
    }

    return number_format((float) $number, $decimal, $decimalPoint, $thousandSep);
}

/**
 * URL encodes (RFC 3986) a string as a path segment or an array as a query string.
 *
 * @param string|array $url A URL or an array of query parameters
 *
 * @return string The URL encoded value
 */
function twig_urlencode_filter($url)
{
    if (is_array($url)) {
        if (defined('PHP_QUERY_RFC3986')) {
            return http_build_query($url, '', '&', PHP_QUERY_RFC3986);
        }

        return http_build_query($url, '', '&');
    }

    return rawurlencode($url);
}

/**
 * Merges an array with another one.
 *
 * <pre>
 *  {% set items = { 'apple': 'fruit', 'orange': 'fruit' } %}
 *
 *  {% set items = items|merge({ 'peugeot': 'car' }) %}
 *
 *  {# items now contains { 'apple': 'fruit', 'orange': 'fruit', 'peugeot': 'car' } #}
 * </pre>
 *
 * @param array|Traversable $arr1 An array
 * @param array|Traversable $arr2 An array
 *
 * @return array The merged array
 */
function twig_array_merge($arr1, $arr2)
{
    if ($arr1 instanceof Traversable) {
        $arr1 = iterator_to_array($arr1);
    } elseif (!is_array($arr1)) {
        throw new Apishka_Templater_Error_Runtime(sprintf('The merge filter only works with arrays or "Traversable", got "%s" as first argument.', gettype($arr1)));
    }

    if ($arr2 instanceof Traversable) {
        $arr2 = iterator_to_array($arr2);
    } elseif (!is_array($arr2)) {
        throw new Apishka_Templater_Error_Runtime(sprintf('The merge filter only works with arrays or "Traversable", got "%s" as second argument.', gettype($arr2)));
    }

    return array_merge($arr1, $arr2);
}

/**
 * Slices a variable.
 *
 * @param Apishka_Templater_Environment $env          A Apishka_Templater_Environment instance
 * @param mixed                         $item         A variable
 * @param int                           $start        Start of the slice
 * @param int                           $length       Size of the slice
 * @param bool                          $preserveKeys Whether to preserve key or not (when the input is an array)
 *
 * @return mixed The sliced variable
 */
function twig_slice(Apishka_Templater_Environment $env, $item, $start, $length = null, $preserveKeys = false)
{
    if ($item instanceof Traversable) {
        if ($item instanceof IteratorAggregate) {
            $item = $item->getIterator();
        }

        if ($start >= 0 && $length >= 0 && $item instanceof Iterator) {
            try {
                return iterator_to_array(new LimitIterator($item, $start, $length === null ? -1 : $length), $preserveKeys);
            } catch (OutOfBoundsException $exception) {
                return array();
            }
        }

        $item = iterator_to_array($item, $preserveKeys);
    }

    if (is_array($item)) {
        return array_slice($item, $start, $length, $preserveKeys);
    }

    $item = (string) $item;

    return (string) mb_substr($item, $start, null === $length ? mb_strlen($item, $env->getCharset()) - $start : $length, $env->getCharset());
}

/**
 * Returns the first element of the item.
 *
 * @param Apishka_Templater_Environment $env  A Apishka_Templater_Environment instance
 * @param mixed                         $item A variable
 *
 * @return mixed The first element of the item
 */
function twig_first(Apishka_Templater_Environment $env, $item)
{
    $elements = twig_slice($env, $item, 0, 1, false);

    return is_string($elements) ? $elements : current($elements);
}

/**
 * Returns the last element of the item.
 *
 * @param Apishka_Templater_Environment $env  A Apishka_Templater_Environment instance
 * @param mixed                         $item A variable
 *
 * @return mixed The last element of the item
 */
function twig_last(Apishka_Templater_Environment $env, $item)
{
    $elements = twig_slice($env, $item, -1, 1, false);

    return is_string($elements) ? $elements : current($elements);
}

/**
 * Joins the values to a string.
 *
 * The separator between elements is an empty string per default, you can define it with the optional parameter.
 *
 * <pre>
 *  {{ [1, 2, 3]|join('|') }}
 *  {# returns 1|2|3 #}
 *
 *  {{ [1, 2, 3]|join }}
 *  {# returns 123 #}
 * </pre>
 *
 * @param array  $value An array
 * @param string $glue  The separator
 *
 * @return string The concatenated string
 */
function twig_join_filter($value, $glue = '')
{
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value, false);
    }

    return implode($glue, (array) $value);
}

/**
 * Splits the string into an array.
 *
 * <pre>
 *  {{ "one,two,three"|split(',') }}
 *  {# returns [one, two, three] #}
 *
 *  {{ "one,two,three,four,five"|split(',', 3) }}
 *  {# returns [one, two, "three,four,five"] #}
 *
 *  {{ "123"|split('') }}
 *  {# returns [1, 2, 3] #}
 *
 *  {{ "aabbcc"|split('', 2) }}
 *  {# returns [aa, bb, cc] #}
 * </pre>
 *
 * @param Apishka_Templater_Environment $env       A Apishka_Templater_Environment instance
 * @param string                        $value     A string
 * @param string                        $delimiter The delimiter
 * @param int                           $limit     The limit
 *
 * @return array The split string as an array
 */
function twig_split_filter(Apishka_Templater_Environment $env, $value, $delimiter, $limit = null)
{
    if (!empty($delimiter)) {
        return null === $limit ? explode($delimiter, $value) : explode($delimiter, $value, $limit);
    }

    if ($limit <= 1) {
        return preg_split('/(?<!^)(?!$)/u', $value);
    }

    $length = mb_strlen($value, $env->getCharset());
    if ($length < $limit) {
        return array($value);
    }

    $r = array();
    for ($i = 0; $i < $length; $i += $limit) {
        $r[] = mb_substr($value, $i, $limit, $env->getCharset());
    }

    return $r;
}

// The '_default' filter is used internally to avoid using the ternary operator
// which costs a lot for big contexts (before PHP 5.4). So, on average,
// a function call is cheaper.
/**
 * @internal
 */
function _twig_default_filter($value, $default = '')
{
    if (twig_test_empty($value)) {
        return $default;
    }

    return $value;
}

/**
 * Returns the keys for the given array.
 *
 * It is useful when you want to iterate over the keys of an array:
 *
 * <pre>
 *  {% for key in array|keys %}
 *      {# ... #}
 *  {% endfor %}
 * </pre>
 *
 * @param array $array An array
 *
 * @return array The keys
 */
function twig_get_array_keys_filter($array)
{
    if ($array instanceof Traversable) {
        return array_keys(iterator_to_array($array));
    }

    if (!is_array($array)) {
        return array();
    }

    return array_keys($array);
}

/**
 * Reverses a variable.
 *
 * @param Apishka_Templater_Environment $env          A Apishka_Templater_Environment instance
 * @param array|Traversable|string      $item         An array, a Traversable instance, or a string
 * @param bool                          $preserveKeys Whether to preserve key or not
 *
 * @return mixed The reversed input
 */
function twig_reverse_filter(Apishka_Templater_Environment $env, $item, $preserveKeys = false)
{
    if ($item instanceof Traversable) {
        return array_reverse(iterator_to_array($item), $preserveKeys);
    }

    if (is_array($item)) {
        return array_reverse($item, $preserveKeys);
    }

    $string = (string) $item;

    $charset = $env->getCharset();

    if ('UTF-8' != $charset) {
        $item = iconv($charset, 'UTF-8', $string);
    }

    preg_match_all('/./u', $item, $matches);

    $string = implode('', array_reverse($matches[0]));

    if ('UTF-8' != $charset) {
        $string = iconv('UTF-8', $charset, $string);
    }

    return $string;
}

/**
 * Sorts an array.
 *
 * @param array|Traversable $array
 *
 * @return array
 */
function twig_sort_filter($array)
{
    if ($array instanceof Traversable) {
        $array = iterator_to_array($array);
    } elseif (!is_array($array)) {
        throw new Apishka_Templater_Error_Runtime(sprintf('The sort filter only works with arrays or "Traversable", got "%s".', gettype($array)));
    }

    asort($array);

    return $array;
}

/**
 * @internal
 */
function twig_in_filter($value, $compare)
{
    if (is_array($compare)) {
        return in_array($value, $compare, is_object($value) || is_resource($value));
    } elseif (is_string($compare) && (is_string($value) || is_int($value) || is_float($value))) {
        return '' === $value || false !== strpos($compare, (string) $value);
    } elseif ($compare instanceof Traversable) {
        return in_array($value, iterator_to_array($compare, false), is_object($value) || is_resource($value));
    }

    return false;
}

/**
 * Escapes a string.
 *
 * @param Apishka_Templater_Environment $env        A Apishka_Templater_Environment instance
 * @param string                        $string     The value to be escaped
 * @param string                        $strategy   The escaping strategy
 * @param string                        $charset    The charset
 * @param bool                          $autoescape Whether the function is called by the auto-escaping feature (true) or by the developer (false)
 *
 * @return string
 */
function twig_escape_filter(Apishka_Templater_Environment $env, $string, $strategy = 'html', $charset = null, $autoescape = false)
{
    if ($autoescape && $string instanceof Apishka_Templater_Markup) {
        return $string;
    }

    if (!is_string($string)) {
        if (is_object($string) && method_exists($string, '__toString')) {
            $string = (string) $string;
        } elseif (in_array($strategy, array('html', 'js', 'css', 'html_attr', 'url'))) {
            return $string;
        }
    }

    if (null === $charset) {
        $charset = $env->getCharset();
    }

    switch ($strategy) {
        case 'html':
            // see http://php.net/htmlspecialchars

            // Using a static variable to avoid initializing the array
            // each time the function is called. Moving the declaration on the
            // top of the function slow downs other escaping strategies.
            static $htmlspecialcharsCharsets;

            if (null === $htmlspecialcharsCharsets) {
                if (defined('HHVM_VERSION')) {
                    $htmlspecialcharsCharsets = array('utf-8' => true, 'UTF-8' => true);
                } else {
                    $htmlspecialcharsCharsets = array(
                        'ISO-8859-1'  => true, 'ISO8859-1' => true,
                        'ISO-8859-15' => true, 'ISO8859-15' => true,
                        'utf-8'       => true, 'UTF-8' => true,
                        'CP866'       => true, 'IBM866' => true, '866' => true,
                        'CP1251'      => true, 'WINDOWS-1251' => true, 'WIN-1251' => true,
                        '1251'        => true,
                        'CP1252'      => true, 'WINDOWS-1252' => true, '1252' => true,
                        'KOI8-R'      => true, 'KOI8-RU' => true, 'KOI8R' => true,
                        'BIG5'        => true, '950' => true,
                        'GB2312'      => true, '936' => true,
                        'BIG5-HKSCS'  => true,
                        'SHIFT_JIS'   => true, 'SJIS' => true, '932' => true,
                        'EUC-JP'      => true, 'EUCJP' => true,
                        'ISO8859-5'   => true, 'ISO-8859-5' => true, 'MACROMAN' => true,
                    );
                }
            }

            if (isset($htmlspecialcharsCharsets[$charset])) {
                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }

            if (isset($htmlspecialcharsCharsets[strtoupper($charset)])) {
                // cache the lowercase variant for future iterations
                $htmlspecialcharsCharsets[$charset] = true;

                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }

            $string = iconv($charset, 'UTF-8', $string);
            $string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return iconv('UTF-8', $charset, $string);

        case 'js':
            // escape all non-alphanumeric characters
            // into their \xHH or \uHHHH representations
            if ('UTF-8' != $charset) {
                $string = iconv($charset, 'UTF-8', $string);
            }

            if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                throw new Apishka_Templater_Error_Runtime('The string to escape is not a valid UTF-8 string.');
            }

            $string = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su', function ($matches) {
                $char = $matches[0];

                // \xHH
                if (!isset($char[1])) {
                    return '\\x' . strtoupper(substr('00' . bin2hex($char), -2));
                }

                // \uHHHH
                $char = twig_convert_encoding($char, 'UTF-16BE', 'UTF-8');

                return '\\u' . strtoupper(substr('0000' . bin2hex($char), -4));
            }, $string);

            if ('UTF-8' != $charset) {
                $string = iconv('UTF-8', $charset, $string);
            }

            return $string;

        case 'css':
            if ('UTF-8' != $charset) {
                $string = iconv($charset, 'UTF-8', $string);
            }

            if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                throw new Apishka_Templater_Error_Runtime('The string to escape is not a valid UTF-8 string.');
            }

            $string = preg_replace_callback('#[^a-zA-Z0-9]#Su', function ($matches) {
                $char = $matches[0];

                // \xHH
                if (!isset($char[1])) {
                    $hex = ltrim(strtoupper(bin2hex($char)), '0');
                    if (0 === strlen($hex)) {
                        $hex = '0';
                    }

                    return '\\' . $hex . ' ';
                }

                // \uHHHH
                $char = twig_convert_encoding($char, 'UTF-16BE', 'UTF-8');

                return '\\' . ltrim(strtoupper(bin2hex($char)), '0') . ' ';
            }, $string);

            if ('UTF-8' != $charset) {
                $string = iconv('UTF-8', $charset, $string);
            }

            return $string;

        case 'html_attr':
            if ('UTF-8' != $charset) {
                $string = iconv($charset, 'UTF-8', $string);
            }

            if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                throw new Apishka_Templater_Error_Runtime('The string to escape is not a valid UTF-8 string.');
            }

            $string = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', function ($matches) {
                /**
                 * This function is adapted from code coming from Zend Framework.
                 *
                 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
                 * @license   http://framework.zend.com/license/new-bsd New BSD License
                 */
                /*
                 * While HTML supports far more named entities, the lowest common denominator
                 * has become HTML5's XML Serialisation which is restricted to the those named
                 * entities that XML supports. Using HTML entities would result in this error:
                 *     XML Parsing Error: undefined entity
                 */
                static $entityMap = array(
                    34 => 'quot', /* quotation mark */
                    38 => 'amp',  /* ampersand */
                    60 => 'lt',   /* less-than sign */
                    62 => 'gt',   /* greater-than sign */
                );

                $chr = $matches[0];
                $ord = ord($chr);

                /*
                 * The following replaces characters undefined in HTML with the
                 * hex entity for the Unicode replacement character.
                 */
                if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r") || ($ord >= 0x7f && $ord <= 0x9f)) {
                    return '&#xFFFD;';
                }

                /*
                 * Check if the current character to escape has a name entity we should
                 * replace it with while grabbing the hex value of the character.
                 */
                if (strlen($chr) == 1) {
                    $hex = strtoupper(substr('00' . bin2hex($chr), -2));
                } else {
                    $chr = twig_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                    $hex = strtoupper(substr('0000' . bin2hex($chr), -4));
                }

                $int = hexdec($hex);
                if (array_key_exists($int, $entityMap)) {
                    return sprintf('&%s;', $entityMap[$int]);
                }

                /*
                 * Per OWASP recommendations, we'll use hex entities for any other
                 * characters where a named entity does not exist.
                 */
                return sprintf('&#x%s;', $hex);
            }, $string);

            if ('UTF-8' != $charset) {
                $string = iconv('UTF-8', $charset, $string);
            }

            return $string;

        case 'url':
            return rawurlencode($string);

        default:
            static $escapers;

            if (null === $escapers) {
                $escapers = $env->getExtension('core')->getEscapers();
            }

            if (isset($escapers[$strategy])) {
                return $escapers[$strategy]($env, $string, $charset);
            }

            $validStrategies = implode(', ', array_merge(array('html', 'js', 'url', 'css', 'html_attr'), array_keys($escapers)));

            throw new Apishka_Templater_Error_Runtime(sprintf('Invalid escaping strategy "%s" (valid ones: %s).', $strategy, $validStrategies));
    }
}

/**
 * @internal
 */
function twig_escape_filter_is_safe(Apishka_Templater_NodeAbstract $filterArgs)
{
    foreach ($filterArgs as $arg) {
        if ($arg instanceof Apishka_Templater_Node_Expression_Constant) {
            return array($arg->getAttribute('value'));
        }

        return array();
    }

    return array('html');
}

function twig_convert_encoding($string, $to, $from)
{
    return iconv($from, $to, $string);
}

/**
 * Returns the length of a variable.
 *
 * @param Apishka_Templater_Environment $env   A Apishka_Templater_Environment instance
 * @param mixed                         $thing A variable
 *
 * @return int The length of the value
 */
function twig_length_filter(Apishka_Templater_Environment $env, $thing)
{
    return is_scalar($thing) ? mb_strlen($thing, $env->getCharset()) : count($thing);
}

/**
 * Converts a string to uppercase.
 *
 * @param Apishka_Templater_Environment $env    A Apishka_Templater_Environment instance
 * @param string                        $string A string
 *
 * @return string The uppercased string
 */
function twig_upper_filter(Apishka_Templater_Environment $env, $string)
{
    return mb_strtoupper($string, $env->getCharset());
}

/**
 * Converts a string to lowercase.
 *
 * @param Apishka_Templater_Environment $env    A Apishka_Templater_Environment instance
 * @param string                        $string A string
 *
 * @return string The lowercased string
 */
function twig_lower_filter(Apishka_Templater_Environment $env, $string)
{
    return mb_strtolower($string, $env->getCharset());
}

/**
 * Returns a titlecased string.
 *
 * @param Apishka_Templater_Environment $env    A Apishka_Templater_Environment instance
 * @param string                        $string A string
 *
 * @return string The titlecased string
 */
function twig_title_string_filter(Apishka_Templater_Environment $env, $string)
{
    return mb_convert_case($string, MB_CASE_TITLE, $env->getCharset());
}

/**
 * Returns a capitalized string.
 *
 * @param Apishka_Templater_Environment $env    A Apishka_Templater_Environment instance
 * @param string                        $string A string
 *
 * @return string The capitalized string
 */
function twig_capitalize_string_filter(Apishka_Templater_Environment $env, $string)
{
    $charset = $env->getCharset();

    return mb_strtoupper(mb_substr($string, 0, 1, $charset), $charset) . mb_strtolower(mb_substr($string, 1, 2147483647, $charset), $charset);
}

/**
 * @internal
 */
function twig_ensure_traversable($seq)
{
    if ($seq instanceof Traversable || is_array($seq)) {
        return $seq;
    }

    return array();
}

/**
 * Checks if a variable is empty.
 *
 * <pre>
 * {# evaluates to true if the foo variable is null, false, or the empty string #}
 * {% if foo is empty %}
 *     {# ... #}
 * {% endif %}
 * </pre>
 *
 * @param mixed $value A variable
 *
 * @return bool true if the value is empty, false otherwise
 */
function twig_test_empty($value)
{
    if ($value instanceof Countable) {
        return 0 == count($value);
    }

    return '' === $value || false === $value || null === $value || array() === $value;
}

/**
 * Checks if a variable is traversable.
 *
 * <pre>
 * {# evaluates to true if the foo variable is an array or a traversable object #}
 * {% if foo is traversable %}
 *     {# ... #}
 * {% endif %}
 * </pre>
 *
 * @param mixed $value A variable
 *
 * @return bool true if the value is traversable
 */
function twig_test_iterable($value)
{
    return $value instanceof Traversable || is_array($value);
}

/**
 * Renders a template.
 *
 * @param Apishka_Templater_Environment $env
 * @param array                         $context
 * @param string|array                  $template      The template to render or an array of templates to try consecutively
 * @param array                         $variables     The variables to pass to the template
 * @param bool                          $withContext
 * @param bool                          $ignoreMissing Whether to ignore missing templates or not
 * @param bool                          $sandboxed     Whether to sandbox the template or not
 *
 * @return string The rendered template
 */
function twig_include(Apishka_Templater_Environment $env, $context, $template, $variables = array(), $withContext = true, $ignoreMissing = false, $sandboxed = false)
{
    $alreadySandboxed = false;
    $sandbox = null;
    if ($withContext) {
        $variables = array_merge($context, $variables);
    }

    if ($isSandboxed = $sandboxed && $env->hasExtension('sandbox')) {
        $sandbox = $env->getExtension('sandbox');
        if (!$alreadySandboxed = $sandbox->isSandboxed()) {
            $sandbox->enableSandbox();
        }
    }

    $result = null;
    try {
        $result = $env->resolveTemplate($template)->render($variables);
    } catch (Apishka_Templater_Error_Loader $e) {
        if (!$ignoreMissing) {
            if ($isSandboxed && !$alreadySandboxed) {
                $sandbox->disableSandbox();
            }

            throw $e;
        }
    }

    if ($isSandboxed && !$alreadySandboxed) {
        $sandbox->disableSandbox();
    }

    return $result;
}

/**
 * Returns a template content without rendering it.
 *
 * @param Apishka_Templater_Environment $env
 * @param string                        $name          The template name
 * @param bool                          $ignoreMissing Whether to ignore missing templates or not
 *
 * @return string The template source
 */
function twig_source(Apishka_Templater_Environment $env, $name, $ignoreMissing = false)
{
    try {
        return $env->getLoader()->getSource($name);
    } catch (Apishka_Templater_Error_Loader $e) {
        if (!$ignoreMissing) {
            throw $e;
        }
    }
}

/**
 * Provides the ability to get constants from instances as well as class/global constants.
 *
 * @param string      $constant The name of the constant
 * @param null|object $object   The object to get the constant from
 *
 * @return string
 */
function twig_constant($constant, $object = null)
{
    if (null !== $object) {
        $constant = get_class($object) . '::' . $constant;
    }

    return constant($constant);
}

/**
 * Batches item.
 *
 * @param array $items An array of items
 * @param int   $size  The size of the batch
 * @param mixed $fill  A value used to fill missing items
 *
 * @return array
 */
function twig_array_batch($items, $size, $fill = null)
{
    if ($items instanceof Traversable) {
        $items = iterator_to_array($items, false);
    }

    $size = ceil($size);

    $result = array_chunk($items, $size, true);

    if (null !== $fill && !empty($result)) {
        $last = count($result) - 1;
        if ($fillCount = $size - count($result[$last])) {
            $result[$last] = array_merge(
                $result[$last],
                array_fill(0, $fillCount, $fill)
            );
        }
    }

    return $result;
}
