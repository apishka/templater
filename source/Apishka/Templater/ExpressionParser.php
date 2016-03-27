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
 * Parses expressions.
 *
 * This parser implements a "Precedence climbing" algorithm.
 *
 * @see http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm
 * @see http://en.wikipedia.org/wiki/Operator-precedence_parser
 */
class Apishka_Templater_ExpressionParser
{
    /**
     * Traits
     */

    use Apishka\EasyExtend\Helper\ByClassNameTrait;

    const OPERATOR_LEFT = 1;
    const OPERATOR_RIGHT = 2;

    private $parser;
    private $unaryOperators;
    private $binaryOperators;

    public function __construct(Apishka_Templater_Parser $parser, array $unaryOperators, array $binaryOperators)
    {
        $this->parser = $parser;
        $this->unaryOperators = $unaryOperators;
        $this->binaryOperators = $binaryOperators;
    }

    public function parseExpression($precedence = 0)
    {
        $expr = $this->getPrimary();
        $token = $this->parser->getCurrentToken();
        while ($this->isBinary($token) && $this->binaryOperators[$token->getValue()]['precedence'] >= $precedence) {
            $op = $this->binaryOperators[$token->getValue()];
            $this->parser->getStream()->next();

            if ($op['type'] == 'test') {
                $class = $op['class'];
                $expr = $class::apishka($this->parser, $expr)
                    ->parseTestExpression()
                ;
            } else {
                $expr1 = $this->parseExpression(
                    self::OPERATOR_LEFT === $op['associativity']
                        ? $op['precedence'] + 1
                        : $op['precedence']
                );

                $class = $op['class'];
                $expr = $class::apishka($expr, $expr1, $token->getLine());
            }

            $token = $this->parser->getCurrentToken();
        }

        if (0 === $precedence) {
            return $this->parseConditionalExpression($expr);
        }

        return $expr;
    }

    private function getPrimary()
    {
        $token = $this->parser->getCurrentToken();

        if ($this->isUnary($token)) {
            $operator = $this->unaryOperators[$token->getValue()];
            $this->parser->getStream()->next();
            $expr = $this->parseExpression($operator['precedence']);
            $class = $operator['class'];

            return $this->parsePostfixExpression($class::apishka($expr, $token->getLine()));
        } elseif ($token->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '(')) {
            $this->parser->getStream()->next();
            $expr = $this->parseExpression();
            $this->parser->getStream()->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ')', 'An opened parenthesis is not properly closed');

            return $this->parsePostfixExpression($expr);
        }

        return $this->parsePrimaryExpression();
    }

    private function parseConditionalExpression($expr)
    {
        while ($this->parser->getStream()->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, '?')) {
            if (!$this->parser->getStream()->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ':')) {
                $expr2 = $this->parseExpression();
                if ($this->parser->getStream()->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ':')) {
                    $expr3 = $this->parseExpression();
                } else {
                    $expr3 = Apishka_Templater_Node_Expression_Constant::apishka('', $this->parser->getCurrentToken()->getLine());
                }
            } else {
                $expr2 = $expr;
                $expr3 = $this->parseExpression();
            }

            $expr = Apishka_Templater_Node_Expression_Conditional::apishka($expr, $expr2, $expr3, $this->parser->getCurrentToken()->getLine());
        }

        return $expr;
    }

    private function isUnary(Apishka_Templater_Token $token)
    {
        return $token->test(Apishka_Templater_Token::OPERATOR_TYPE) && isset($this->unaryOperators[$token->getValue()]);
    }

    private function isBinary(Apishka_Templater_Token $token)
    {
        return $token->test(Apishka_Templater_Token::OPERATOR_TYPE) && isset($this->binaryOperators[$token->getValue()]);
    }

    public function parsePrimaryExpression()
    {
        $token = $this->parser->getCurrentToken();
        switch ($token->getType()) {
            case Apishka_Templater_Token::NAME_TYPE:
                $this->parser->getStream()->next();
                switch ($token->getValue()) {
                    case 'true':
                    case 'TRUE':
                        $node = Apishka_Templater_Node_Expression_Constant::apishka(true, $token->getLine());
                        break;

                    case 'false':
                    case 'FALSE':
                        $node = Apishka_Templater_Node_Expression_Constant::apishka(false, $token->getLine());
                        break;

                    case 'none':
                    case 'NONE':
                    case 'null':
                    case 'NULL':
                        $node = Apishka_Templater_Node_Expression_Constant::apishka(null, $token->getLine());
                        break;

                    default:
                        if ('(' === $this->parser->getCurrentToken()->getValue()) {
                            $node = $this->getFunctionNode($token->getValue(), $token->getLine());
                        } else {
                            $node = Apishka_Templater_Node_Expression_Name::apishka($token->getValue(), $token->getLine());
                        }
                }
                break;

            case Apishka_Templater_Token::NUMBER_TYPE:
                $this->parser->getStream()->next();
                $node = Apishka_Templater_Node_Expression_Constant::apishka($token->getValue(), $token->getLine());
                break;

            case Apishka_Templater_Token::STRING_TYPE:
            case Apishka_Templater_Token::INTERPOLATION_START_TYPE:
                $node = $this->parseStringExpression();
                break;

            case Apishka_Templater_Token::OPERATOR_TYPE:
                if (preg_match(Apishka_Templater_Lexer::REGEX_NAME, $token->getValue(), $matches) && $matches[0] == $token->getValue()) {
                    // in this context, string operators are variable names
                    $this->parser->getStream()->next();
                    $node = Apishka_Templater_Node_Expression_Name::apishka($token->getValue(), $token->getLine());
                    break;
                } elseif (isset($this->unaryOperators[$token->getValue()])) {
                    $class = $this->unaryOperators[$token->getValue()]['class'];

                    $ref = new ReflectionClass($class);
                    $negClass = 'Apishka_Templater_Node_Expression_Unary_Neg';
                    $posClass = 'Apishka_Templater_Node_Expression_Unary_Pos';
                    if (!(in_array($ref->getName(), array($negClass, $posClass)) || $ref->isSubclassOf($negClass) || $ref->isSubclassOf($posClass))) {
                        throw new Apishka_Templater_Error_Syntax(sprintf('Unexpected unary operator "%s".', $token->getValue()), $token->getLine(), $this->parser->getFilename());
                    }

                    $this->parser->getStream()->next();
                    $expr = $this->parsePrimaryExpression();

                    $node = new $class($expr, $token->getLine());
                    break;
                }

            default:
                if ($token->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '[')) {
                    $node = $this->parseArrayExpression();
                } elseif ($token->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '{')) {
                    $node = $this->parseHashExpression();
                } else {
                    throw new Apishka_Templater_Error_Syntax(sprintf('Unexpected token "%s" of value "%s".', Apishka_Templater_Token::typeToEnglish($token->getType()), $token->getValue()), $token->getLine(), $this->parser->getFilename());
                }
        }

        return $this->parsePostfixExpression($node);
    }

    public function parseStringExpression()
    {
        $stream = $this->parser->getStream();

        $nodes = array();
        // a string cannot be followed by another string in a single expression
        $nextCanBeString = true;
        while (true) {
            if ($nextCanBeString && $token = $stream->nextIf(Apishka_Templater_Token::STRING_TYPE)) {
                $nodes[] = Apishka_Templater_Node_Expression_Constant::apishka($token->getValue(), $token->getLine());
                $nextCanBeString = false;
            } elseif ($stream->nextIf(Apishka_Templater_Token::INTERPOLATION_START_TYPE)) {
                $nodes[] = $this->parseExpression();
                $stream->expect(Apishka_Templater_Token::INTERPOLATION_END_TYPE);
                $nextCanBeString = true;
            } else {
                break;
            }
        }

        $expr = array_shift($nodes);
        foreach ($nodes as $node) {
            $expr = Apishka_Templater_Node_Expression_Binary_Concat::apishka($expr, $node, $node->getLine());
        }

        return $expr;
    }

    public function parseArrayExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, '[', 'An array element was expected');

        $node = Apishka_Templater_Node_Expression_Array::apishka(array(), $stream->getCurrent()->getLine());
        $first = true;
        while (!$stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, ']')) {
            if (!$first) {
                $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ',', 'An array element must be followed by a comma');

                // trailing ,?
                if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, ']')) {
                    break;
                }
            }
            $first = false;

            $node->addElement($this->parseExpression());
        }
        $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ']', 'An opened array is not properly closed');

        return $node;
    }

    public function parseHashExpression()
    {
        $stream = $this->parser->getStream();
        $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, '{', 'A hash element was expected');

        $node = Apishka_Templater_Node_Expression_Array::apishka(array(), $stream->getCurrent()->getLine());
        $first = true;
        while (!$stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '}')) {
            if (!$first) {
                $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ',', 'A hash value must be followed by a comma');

                // trailing ,?
                if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '}')) {
                    break;
                }
            }
            $first = false;

            // a hash key can be:
            //
            //  * a number -- 12
            //  * a string -- 'a'
            //  * a name, which is equivalent to a string -- a
            //  * an expression, which must be enclosed in parentheses -- (1 + 2)
            if (($token = $stream->nextIf(Apishka_Templater_Token::STRING_TYPE)) || ($token = $stream->nextIf(Apishka_Templater_Token::NAME_TYPE)) || $token = $stream->nextIf(Apishka_Templater_Token::NUMBER_TYPE)) {
                $key = Apishka_Templater_Node_Expression_Constant::apishka($token->getValue(), $token->getLine());
            } elseif ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '(')) {
                $key = $this->parseExpression();
            } else {
                $current = $stream->getCurrent();

                throw new Apishka_Templater_Error_Syntax(sprintf('A hash key must be a quoted string, a number, a name, or an expression enclosed in parentheses (unexpected token "%s" of value "%s".', Apishka_Templater_Token::typeToEnglish($current->getType()), $current->getValue()), $current->getLine(), $this->parser->getFilename());
            }

            $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ':', 'A hash key must be followed by a colon (:)');
            $value = $this->parseExpression();

            $node->addElement($value, $key);
        }
        $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, '}', 'An opened hash is not properly closed');

        return $node;
    }

    public function parsePostfixExpression($node)
    {
        while (true) {
            $token = $this->parser->getCurrentToken();
            if ($token->getType() == Apishka_Templater_Token::PUNCTUATION_TYPE) {
                if ('.' == $token->getValue() || '[' == $token->getValue()) {
                    $node = $this->parseSubscriptExpression($node);
                } elseif ('|' == $token->getValue()) {
                    $node = $this->parseFilterExpression($node);
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return $node;
    }

    public function getFunctionNode($name, $line)
    {
        switch ($name) {
            case 'parent':
            {
                $this->parseArguments();
                if (!count($this->parser->getBlockStack())) {
                    throw new Apishka_Templater_Error_Syntax('Calling "parent" outside a block is forbidden.', $line, $this->parser->getFilename());
                }

                if (!$this->parser->getParent() && !$this->parser->hasTraits()) {
                    throw new Apishka_Templater_Error_Syntax('Calling "parent" on a template that does not extend nor "use" another template is forbidden.', $line, $this->parser->getFilename());
                }

                return Apishka_Templater_Node_Expression_Parent::apishka($this->parser->peekBlockStack(), $line);
            }
            case 'block':
            {
                $args = $this->parseArguments(true, false, true);
                if (count($args) < 1) {
                    throw new Apishka_Templater_Error_Syntax('The "block" function takes at least one argument (the variable and the attributes).', $line, $this->parser->getFilename());
                }

                $block_name = $args->getNode('__first_arg__');
                $args->removeNode('__first_arg__');

                return Apishka_Templater_Node_Expression_BlockReference::apishka(
                    $block_name,
                    $args,
                    false,
                    $line
                );
            }
            case 'attribute':
            {
                $args = $this->parseArguments();
                if (count($args) < 2) {
                    throw new Apishka_Templater_Error_Syntax('The "attribute" function takes at least two arguments (the variable and the attributes).', $line, $this->parser->getFilename());
                }

                return Apishka_Templater_Node_Expression_GetAttr::apishka(
                    $args->getNode(0),
                    $args->getNode(1),
                    count($args) > 2 ? $args->getNode(2) : null,
                    Apishka_Templater_TemplateAbstract::ANY_CALL,
                    $line
                );
            }
            default:
            {
                return $this->getFunctionNodeDefault($name, $line);
            }
        }
    }

    /**
     * Get function node default
     *
     * @param string $name
     * @param int    $line
     *
     * @return Apishka_Templater_NodeAbstract
     */

    protected function getFunctionNodeDefault($name, $line = null)
    {
        if (null !== $alias = $this->parser->getImportedSymbol('function', $name)) {
            $arguments = Apishka_Templater_Node_Expression_Array::apishka(array(), $line);
            foreach ($this->parseArguments() as $n) {
                $arguments->addElement($n);
            }

            $node = Apishka_Templater_Node_Expression_MethodCall::apishka($alias['node'], $alias['name'], $arguments, $line);
            $node->setAttribute('safe', true);

            return $node;
        }

        $args = $this->parseArguments(true);
        $class = $this->getFunctionNodeClass($name, $line);

        return $class::apishka($name, $args, $line);
    }

    public function parseSubscriptExpression($node)
    {
        $stream = $this->parser->getStream();
        $token = $stream->next();
        $lineno = $token->getLine();
        $arguments = Apishka_Templater_Node_Expression_Array::apishka(array(), $lineno);
        $type = Apishka_Templater_TemplateAbstract::ANY_CALL;
        if ($token->getValue() == '.') {
            $token = $stream->next();
            if (
                $token->getType() == Apishka_Templater_Token::NAME_TYPE
                ||
                $token->getType() == Apishka_Templater_Token::NUMBER_TYPE
                ||
                ($token->getType() == Apishka_Templater_Token::OPERATOR_TYPE && preg_match(Apishka_Templater_Lexer::REGEX_NAME, $token->getValue()))
            ) {
                $arg = Apishka_Templater_Node_Expression_Constant::apishka($token->getValue(), $lineno);

                if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '(')) {
                    $type = Apishka_Templater_TemplateAbstract::METHOD_CALL;
                    foreach ($this->parseArguments() as $n) {
                        $arguments->addElement($n);
                    }
                }
            } else {
                throw new Apishka_Templater_Error_Syntax('Expected name or number', $lineno, $this->parser->getFilename());
            }

            if ($node instanceof Apishka_Templater_Node_Expression_Name && null !== $this->parser->getImportedSymbol('template', $node->getAttribute('name'))) {
                if (!$arg instanceof Apishka_Templater_Node_Expression_Constant) {
                    throw new Apishka_Templater_Error_Syntax(sprintf('Dynamic macro names are not supported (called on "%s").', $node->getAttribute('name')), $token->getLine(), $this->parser->getFilename());
                }

                $name = $arg->getAttribute('value');

                $node = Apishka_Templater_Node_Expression_MethodCall::apishka($node, 'macro_' . $name, $arguments, $lineno);
                $node->setAttribute('safe', true);

                return $node;
            }
        } else {
            $type = Apishka_Templater_TemplateAbstract::ARRAY_CALL;

            // slice?
            $slice = false;
            if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, ':')) {
                $slice = true;
                $arg = Apishka_Templater_Node_Expression_Constant::apishka(0, $token->getLine());
            } else {
                $arg = $this->parseExpression();
            }

            if ($stream->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ':')) {
                $slice = true;
            }

            if ($slice) {
                if ($stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, ']')) {
                    $length = Apishka_Templater_Node_Expression_Constant::apishka(null, $token->getLine());
                } else {
                    $length = $this->parseExpression();
                }

                $class = $this->getFilterNodeClass('slice', $token->getLine());
                $arguments = Apishka_Templater_Node::apishka(array($arg, $length));
                $filter = $class::apishka($node, Apishka_Templater_Node_Expression_Constant::apishka('slice', $token->getLine()), $arguments, $token->getLine());

                $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ']');

                return $filter;
            }

            $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, ']');
        }

        return Apishka_Templater_Node_Expression_GetAttr::apishka($node, $arg, $arguments, $type, $lineno);
    }

    public function parseFilterExpression($node)
    {
        $this->parser->getStream()->next();

        return $this->parseFilterExpressionRaw($node);
    }

    public function parseFilterExpressionRaw($node, $tag = null)
    {
        while (true) {
            $token = $this->parser->getStream()->expect(Apishka_Templater_Token::NAME_TYPE);

            $name = Apishka_Templater_Node_Expression_Constant::apishka($token->getValue(), $token->getLine());
            if (!$this->parser->getStream()->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '(')) {
                $arguments = Apishka_Templater_Node::apishka();
            } else {
                $arguments = $this->parseArguments(true);
            }

            $class = $this->getFilterNodeClass($name->getAttribute('value'), $token->getLine());

            $node = $class::apishka($node, $name, $arguments, $token->getLine(), $tag);

            if (!$this->parser->getStream()->test(Apishka_Templater_Token::PUNCTUATION_TYPE, '|')) {
                break;
            }

            $this->parser->getStream()->next();
        }

        return $node;
    }

    /**
     * Parses arguments.
     *
     * @param bool $namedArguments Whether to allow named arguments or not
     * @param bool $definition     Whether we are parsing arguments for a function definition
     * @param bool $firstNoNamed   Whether first argument no named and other has name
     *
     * @throws Apishka_Templater_Error_Syntax
     *
     * @return Apishka_Templater_Node
     */
    public function parseArguments($namedArguments = false, $definition = false, $firstNoNamed = false)
    {
        $args = array();
        $stream = $this->parser->getStream();

        $stream->expect(Apishka_Templater_Token::PUNCTUATION_TYPE, '(', 'A list of arguments must begin with an opening parenthesis');
        while (!$stream->test(Apishka_Templater_Token::PUNCTUATION_TYPE, ')')) {
            if (!empty($args)) {
                $stream->expect(
                    Apishka_Templater_Token::PUNCTUATION_TYPE,
                    ',',
                    'Arguments must be separated by a comma'
                );
            }

            if ($firstNoNamed && empty($args)) {
                $value = $this->parseExpression();
                $args['__first_arg__'] = $value;
                continue;
            }

            if ($definition) {
                $token = $stream->expect(
                    Apishka_Templater_Token::NAME_TYPE,
                    null,
                    'An argument must be a name'
                );

                $value = Apishka_Templater_Node_Expression_Name::apishka(
                    $token->getValue(),
                    $this->parser->getCurrentToken()->getLine()
                );
            } else {
                $value = $this->parseExpression();
            }

            $name = null;
            if ($namedArguments && $token = $stream->nextIf(Apishka_Templater_Token::OPERATOR_TYPE, '=')) {
                if (!$value instanceof Apishka_Templater_Node_Expression_Name) {
                    throw new Apishka_Templater_Error_Syntax(
                        sprintf(
                            'A parameter name must be a string, "%s" given.',
                            get_class($value)
                        ),
                        $token->getLine(),
                        $this->parser->getFilename()
                    );
                }

                $name = $value->getAttribute('name');

                if ($definition) {
                    $value = $this->parsePrimaryExpression();

                    if (!$this->checkConstantExpression($value)) {
                        throw new Apishka_Templater_Error_Syntax(
                            sprintf('A default value for an argument must be a constant (a boolean, a string, a number, or an array).'),
                            $token->getLine(),
                            $this->parser->getFilename()
                        );
                    }
                } else {
                    $value = $this->parseExpression();
                }
            }

            if ($definition) {
                if (null === $name) {
                    $name = $value->getAttribute('name');
                    $value = Apishka_Templater_Node_Expression_Constant::apishka(
                        null,
                        $this->parser->getCurrentToken()->getLine()
                    );
                }

                $args[$name] = $value;
            } else {
                if (null === $name) {
                    $args[] = $value;
                } else {
                    $args[$name] = $value;
                }
            }
        }

        $stream->expect(
            Apishka_Templater_Token::PUNCTUATION_TYPE,
            ')',
            'A list of arguments must be closed by a parenthesis'
        );

        return Apishka_Templater_Node::apishka($args);
    }

    public function parseAssignmentExpression()
    {
        $targets = array();
        while (true) {
            $token = $this->parser->getStream()->expect(Apishka_Templater_Token::NAME_TYPE, null, 'Only variables can be assigned to');
            if (in_array($token->getValue(), array('true', 'false', 'none'))) {
                throw new Apishka_Templater_Error_Syntax(sprintf('You cannot assign a value to "%s".', $token->getValue()), $token->getLine(), $this->parser->getFilename());
            }
            $targets[] = Apishka_Templater_Node_Expression_AssignName::apishka($token->getValue(), $token->getLine());

            if (!$this->parser->getStream()->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }

        return Apishka_Templater_Node::apishka($targets);
    }

    public function parseMultitargetExpression()
    {
        $targets = array();
        while (true) {
            $targets[] = $this->parseExpression();
            if (!$this->parser->getStream()->nextIf(Apishka_Templater_Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }

        return Apishka_Templater_Node::apishka($targets);
    }

    private function getFunctionNodeClass($name, $line)
    {
        $env = $this->parser->getEnvironment();

        if (false === $function = $env->getFunction($name)) {
            $e = new Apishka_Templater_Error_Syntax(sprintf('Unknown "%s" function.', $name), $line, $this->parser->getFilename());
            $e->addSuggestions($name, array_keys($env->getFunctions()));

            throw $e;
        }

        if ($function->isDeprecated()) {
            $message = sprintf('Twig Function "%s" is deprecated', $function->getName());
            if (!is_bool($function->getDeprecatedVersion())) {
                $message .= sprintf(' since version %s', $function->getDeprecatedVersion());
            }
            if ($function->getAlternative()) {
                $message .= sprintf('. Use "%s" instead', $function->getAlternative());
            }
            $message .= sprintf(' in %s at line %d.', $this->parser->getFilename(), $line);

            @trigger_error($message, E_USER_DEPRECATED);
        }

        return $function->getNodeClass();
    }

    private function getFilterNodeClass($name, $line)
    {
        $env = $this->parser->getEnvironment();

        if (false === $filter = $env->getFilter($name)) {
            $e = new Apishka_Templater_Error_Syntax(sprintf('Unknown "%s" filter.', $name), $line, $this->parser->getFilename());
            $e->addSuggestions($name, array_keys($env->getFilters()));

            throw $e;
        }

        if ($filter->isDeprecated()) {
            $message = sprintf('Twig Filter "%s" is deprecated', $filter->getName());
            if (!is_bool($filter->getDeprecatedVersion())) {
                $message .= sprintf(' since version %s', $filter->getDeprecatedVersion());
            }
            if ($filter->getAlternative()) {
                $message .= sprintf('. Use "%s" instead', $filter->getAlternative());
            }
            $message .= sprintf(' in %s at line %d.', $this->parser->getFilename(), $line);

            @trigger_error($message, E_USER_DEPRECATED);
        }

        return $filter->getNodeClass();
    }

    // checks that the node only contains "constant" elements
    private function checkConstantExpression(Apishka_Templater_NodeAbstract $node)
    {
        if (!($node instanceof Apishka_Templater_Node_Expression_Constant || $node instanceof Apishka_Templater_Node_Expression_Array
            || $node instanceof Apishka_Templater_Node_Expression_Unary_Neg || $node instanceof Apishka_Templater_Node_Expression_Unary_Pos
        )) {
            return false;
        }

        foreach ($node as $n) {
            if (!$this->checkConstantExpression($n)) {
                return false;
            }
        }

        return true;
    }
}
