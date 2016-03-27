<?php

/**
 * Apishka templater node expression binary test is not
 */

class Apishka_Templater_Node_Expression_Binary_TestIsNot extends Apishka_Templater_Node_Expression_BinaryTestAbstract implements Apishka_Templater_Node_Expression_BinaryTestInterface
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
            'is not',
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
     * @param Apishka_Templater_Parser       $parser
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
        $node = (new Apishka_Templater_Node_Expression_Binary_TestIs($this->_parser, $this->_node))
            ->parseTestExpression()
        ;

        return Apishka_Templater_Node_Expression_Unary_Not::apishka(
            $node,
            $this->_parser->getCurrentToken()->getLine()
        );
    }
}
