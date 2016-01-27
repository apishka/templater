<?php

/**
 * Apishka templater node expression binary test is
 *
 * @uses Apishka_Templater_Node_Expression_BinaryTestInterface
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Expression_Binary_TestIs extends Apishka_Templater_Node_Expression_BinaryTestAbstract implements Apishka_Templater_Node_Expression_BinaryTestInterface
{
    /**
     * Parser
     *
     * @var Apishka_Templater_Parser
     */

    private $_parser;

    /**
     * Apishka_Templater_NodeAbstract
     *
     * @var mixed
     */

    private $_node;

    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames()
    {
        return array(
            'is',
        );
    }

    /**
     * Get precedence
     *
     * @return int
     */

    public function getPrecedence()
    {
        return 100;
    }

    /**
     * Get associativity
     *
     * @return int
     */

    public function getAssociativity()
    {
        return Apishka_Templater_ExpressionParser::OPERATOR_LEFT;
    }

    /**
     * Get type name
     *
     * @return string
     */

    public function getTypeName()
    {
        return 'test';
    }

    /**
     * Construct
     *
     * @param Apishka_Templater_Parser $parser
     * @param Apishka_Templater_NodeAbstract $node
     */

    public function __construct(Apishka_Templater_Parser $parser, Apishka_Templater_NodeAbstract $node)
    {
        $this->_parser = $parser;
        $this->_node = $node;
    }

    /**
     * Parse test expression
     *
     * @return Apishka_Templater_NodeAbstract
     */

    public function parseTestExpression()
    {
        $stream = $this->_parser->getStream();
        $test   = $this->getTest(
            $this->_parser,
            $this->_node->getLine()
        );

        $class  = $test->getNodeClass();

        $arguments = null;
        if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '('))
            $arguments = $this->_parser->getExpressionParser()->parseArguments(true);

        return $class::apishka(
            $this->_node,
            $test->getName(),
            $arguments,
            $this->_parser->getCurrentToken()->getLine()
        );
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

        $e = new Apishka_Templater_Error_Syntax(
            sprintf('Unknown "%s" test.', $name), $line, $parser->getFilename()
        );
        $e->addSuggestions($name, array_keys($env->getTests()));

        throw $e;
    }
}
