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
 * Default parser implementation.
 */
class Apishka_Templater_Parser
{
    private $stack = array();
    private $stream;
    private $parent;
    private $handlers;
    private $visitors;
    private $expressionParser;
    private $blocks;
    private $blockStack;
    private $macros;
    private $env;
    private $importedSymbols;
    private $traits;
    private $embeddedTemplates = array();

    /**
     * Constructor.
     *
     * @param Apishka_Templater_Environment $env A Apishka_Templater_Environment instance
     */
    public function __construct(Apishka_Templater_Environment $env)
    {
        $this->env = $env;
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function getVarName()
    {
        return sprintf('__internal_%s', hash('sha256', uniqid(mt_rand(), true), false));
    }

    public function getFilename()
    {
        return $this->stream->getFilename();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Apishka_Templater_TokenStream $stream, $test = null, $dropNeedle = false)
    {
        // push all variables into the stack to keep the current state of the parser
        // using get_object_vars() instead of foreach would lead to https://bugs.php.net/71336
        $vars = array();
        foreach ($this as $k => $v) {
            $vars[$k] = $v;
        }

        unset($vars['stack'], $vars['env'], $vars['handlers'], $vars['visitors'], $vars['expressionParser'], $vars['reservedMacroNames']);
        $this->stack[] = $vars;

        // tag handlers
        if (null === $this->handlers) {
            $this->handlers = array();
            foreach ($this->env->getTokenParsers() as $handler) {
                $handler->setParser($this);

                $this->handlers[$handler->getTag()] = $handler;
            }
        }

        // node visitors
        if (null === $this->visitors) {
            $this->visitors = $this->env->getNodeVisitors();
        }

        if ($this->expressionParser === null) {
            $this->expressionParser = Apishka_Templater_ExpressionParser::apishka($this, $this->env->getUnaryOperators(), $this->env->getBinaryOperators());
        }

        $this->stream = $stream;
        $this->parent = null;
        $this->blocks = array();
        $this->macros = array();
        $this->traits = array();
        $this->blockStack = array();
        $this->importedSymbols = array(array());
        $this->embeddedTemplates = array();

        try {
            $body = $this->subparse($test, $dropNeedle);

            if (null !== $this->parent && null === $body = $this->filterBodyNodes($body)) {
                $body = Apishka_Templater_Node::apishka();
            }
        } catch (Apishka_Templater_Error_Syntax $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($this->getFilename());
            }

            if (!$e->getTemplateLine()) {
                $e->setTemplateLine($this->stream->getCurrent()->getLine());
            }

            throw $e;
        }

        $node = Apishka_Templater_Node_Module::apishka(
            Apishka_Templater_Node_Body::apishka(array($body)),
            $this->parent,
            Apishka_Templater_Node::apishka($this->blocks),
            Apishka_Templater_Node::apishka($this->macros),
            Apishka_Templater_Node::apishka($this->traits),
            $this->embeddedTemplates,
            $this->getFilename()
        );

        $traverser = new Apishka_Templater_NodeTraverser($this->env, $this->visitors);

        $node = $traverser->traverse($node);

        // restore previous stack so previous parse() call can resume working
        foreach (array_pop($this->stack) as $key => $val) {
            $this->$key = $val;
        }

        return $node;
    }

    public function subparse($test, $dropNeedle = false)
    {
        $lineno = $this->getCurrentToken()->getLine();
        $rv = array();
        while (!$this->stream->isEOF()) {
            switch ($this->getCurrentToken()->getType()) {
                case Apishka_Templater_Token::TEXT_TYPE:
                    $token = $this->stream->next();
                    $rv[] = Apishka_Templater_Node_Text::apishka($token->getValue(), $token->getLine());
                    break;

                case Apishka_Templater_Token::VAR_START_TYPE:
                    $token = $this->stream->next();
                    $expr = $this->expressionParser->parseExpression();
                    $this->stream->expect(Apishka_Templater_Token::VAR_END_TYPE);
                    $rv[] = Apishka_Templater_Node_Print::apishka($expr, $token->getLine());
                    break;

                case Apishka_Templater_Token::BLOCK_START_TYPE:
                    $this->stream->next();
                    $token = $this->getCurrentToken();

                    if ($token->getType() !== Apishka_Templater_Token::NAME_TYPE) {
                        throw new Apishka_Templater_Error_Syntax('A block must start with a tag name.', $token->getLine(), $this->getFilename());
                    }

                    if (null !== $test && $test($token)) {
                        if ($dropNeedle) {
                            $this->stream->next();
                        }

                        if (1 === count($rv)) {
                            return $rv[0];
                        }

                        return Apishka_Templater_Node::apishka($rv, array(), $lineno);
                    }

                    if (!isset($this->handlers[$token->getValue()])) {
                        if (null !== $test) {
                            $e = new Apishka_Templater_Error_Syntax(sprintf('Unexpected "%s" tag', $token->getValue()), $token->getLine(), $this->getFilename());

                            if (is_array($test) && isset($test[0]) && $test[0] instanceof Apishka_Templater_TokenParserInterface) {
                                $e->appendMessage(sprintf(' (expecting closing tag for the "%s" tag defined near line %s).', $test[0]->getTag(), $lineno));
                            }
                        } else {
                            $e = new Apishka_Templater_Error_Syntax(sprintf('Unknown "%s" tag.', $token->getValue()), $token->getLine(), $this->getFilename());
                            $e->addSuggestions($token->getValue(), array_keys($this->env->getTags()));
                        }

                        throw $e;
                    }

                    $this->stream->next();

                    $subparser = $this->handlers[$token->getValue()];
                    $node = $subparser->parse($token);
                    if (null !== $node) {
                        $rv[] = $node;
                    }
                    break;

                default:
                    throw new Apishka_Templater_Error_Syntax('Lexer or parser ended up in unsupported state.', 0, $this->getFilename());
            }
        }

        if (1 === count($rv)) {
            return $rv[0];
        }

        return Apishka_Templater_Node::apishka($rv, array(), $lineno);
    }

    public function addHandler($name, $class)
    {
        $this->handlers[$name] = $class;
    }

    public function addNodeVisitor(Apishka_Templater_NodeVisitorInterface $visitor)
    {
        $this->visitors[] = $visitor;
    }

    public function getBlockStack()
    {
        return $this->blockStack;
    }

    public function peekBlockStack()
    {
        return $this->blockStack[count($this->blockStack) - 1];
    }

    public function popBlockStack()
    {
        array_pop($this->blockStack);
    }

    public function pushBlockStack($name)
    {
        $this->blockStack[] = $name;
    }

    public function hasBlock($name)
    {
        return isset($this->blocks[$name]);
    }

    public function getBlock($name)
    {
        return $this->blocks[$name];
    }

    public function setBlock($name, Apishka_Templater_Node_Block $value)
    {
        $this->blocks[$name] = Apishka_Templater_Node_Body::apishka(array($value), array(), $value->getLine());
    }

    public function hasMacro($name)
    {
        return isset($this->macros[$name]);
    }

    /**
     * @deprecated since 2.0. Will be removed in 3.0. There is no reserved macro names anymore
     */
    public function isReservedMacroName($name)
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 2.0 and will be removed in 3.0.', E_USER_DEPRECATED);

        return false;
    }

    public function addTrait($trait)
    {
        $this->traits[] = $trait;
    }

    public function hasTraits()
    {
        return count($this->traits) > 0;
    }

    public function embedTemplate(Apishka_Templater_Node_Module $template)
    {
        $template->setIndex(mt_rand());

        $this->embeddedTemplates[] = $template;
    }

    public function addImportedSymbol($type, $alias, $name = null, Apishka_Templater_Node_ExpressionAbstract $node = null)
    {
        $this->importedSymbols[0][$type][$alias] = array('name' => $name, 'node' => $node);
    }

    public function getImportedSymbol($type, $alias)
    {
        foreach ($this->importedSymbols as $functions) {
            if (isset($functions[$type][$alias])) {
                return $functions[$type][$alias];
            }
        }
    }

    public function isMainScope()
    {
        return 1 === count($this->importedSymbols);
    }

    public function pushLocalScope()
    {
        array_unshift($this->importedSymbols, array());
    }

    public function popLocalScope()
    {
        array_shift($this->importedSymbols);
    }

    /**
     * Gets the expression parser.
     *
     * @return Apishka_Templater_ExpressionParser The expression parser
     */
    public function getExpressionParser()
    {
        return $this->expressionParser;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Gets the token stream.
     *
     * @return Apishka_Templater_TokenStream The token stream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Gets the current token.
     *
     * @return Apishka_Templater_Token The current token
     */
    public function getCurrentToken()
    {
        return $this->stream->getCurrent();
    }

    private function filterBodyNodes(Apishka_Templater_NodeAbstract $node)
    {
        // check that the body does not contain non-empty output nodes
        if (
            ($node instanceof Apishka_Templater_Node_Text && !ctype_space($node->getAttribute('data')))
            ||
            (!$node instanceof Apishka_Templater_Node_Text && !$node instanceof Apishka_Templater_Node_BlockReference && $node instanceof Apishka_Templater_NodeOutputInterface)
        ) {
            if (false !== strpos((string) $node, chr(0xEF) . chr(0xBB) . chr(0xBF))) {
                throw new Apishka_Templater_Error_Syntax('A template that extends another one cannot have a body but a byte order mark (BOM) has been detected; it must be removed.', $node->getLine(), $this->getFilename());
            }

            throw new Apishka_Templater_Error_Syntax('A template that extends another one cannot have a body.', $node->getLine(), $this->getFilename());
        }

        // bypass "set" nodes as they "capture" the output
        if ($node instanceof Apishka_Templater_Node_Set) {
            return $node;
        }

        if ($node instanceof Apishka_Templater_NodeOutputInterface) {
            return;
        }

        foreach ($node as $k => $n) {
            if (null !== $n && null === $this->filterBodyNodes($n)) {
                $node->removeNode($k);
            }
        }

        return $node;
    }
}
