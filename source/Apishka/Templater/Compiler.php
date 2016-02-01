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
 * Compiles a node to PHP code.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */

class Apishka_Templater_Compiler
{
    /**
     * Debug info
     *
     * @var array
     */

    private $_debug_info = array();

    /**
     * Last line
     *
     * @var int
     */

    private $_last_line;

    /**
     * Indentation
     *
     * @var int
     */

    private $_indentation;

    /**
     * Env
     *
     * @var Apishka_Templater_Environment
     */

    private $_env;

    /**
     * Source
     *
     * @var mixed
     */

    private $_source;

    /**
     * Source offset
     *
     * @var int
     */

    private $_source_offset;

    /**
     * Source line
     *
     * @var int
     */

    private $_source_line;

    /**
     * Filename
     *
     * @var string
     */

    private $_filename;

    /**
     * Constructor.
     *
     * @param Apishka_Templater_Environment $env The twig environment instance
     */

    public function __construct(Apishka_Templater_Environment $env)
    {
        $this->_env = $env;
    }

    /**
     * Get filename
     *
     * @return string
     */

    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Returns the environment instance related to this compiler.
     *
     * @return Apishka_Templater_Environment The environment instance
     */

    public function getEnvironment()
    {
        return $this->_env;
    }

    /**
     * Gets the current PHP code after compilation.
     *
     * @return string The PHP code
     */

    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Compiles a node.
     *
     * @param Apishka_Templater_Node $node        The node to compile
     * @param int                    $indentation The current indentation
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function compile(Apishka_Templater_NodeAbstract $node, $indentation = 0)
    {
        $this->_last_line = null;
        $this->_source = '';
        $this->_debug_info = array();
        $this->_source_offset = 0;
        // source code starts at 1 (as we then increment it when we encounter new lines)
        $this->_source_line = 1;
        $this->_indentation = $indentation;

        if ($node instanceof Apishka_Templater_Node_Module)
            $this->_filename = $node->getAttribute('filename');

        $node->compile($this);

        return $this;
    }

    /**
     * Subcompile
     *
     * @param Apishka_Templater_NodeAbstract $node
     * @param bool                           $raw
     *
     * @return Apishka_Templater_Compiler
     */

    public function subcompile(Apishka_Templater_NodeAbstract $node, $raw = true)
    {
        if (false === $raw)
            $this->addIndentation();

        $node->compile($this);

        return $this;
    }

    /**
     * Adds a raw string to the compiled code.
     *
     * @param string $string The string
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function raw($string)
    {
        $this->_source .= $string;

        return $this;
    }

    /**
     * Writes a string to the compiled code by adding indentation.
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function write()
    {
        $strings = func_get_args();
        foreach ($strings as $string)
        {
            $this->addIndentation();
            $this->_source .= $string;
        }

        return $this;
    }

    /**
     * Appends an indentation to the current PHP code after compilation.
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function addIndentation()
    {
        $this->_source .= str_repeat(' ', $this->_indentation * 4);

        return $this;
    }

    /**
     * Adds a quoted string to the compiled code.
     *
     * @param string $value The string
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function string($value)
    {
        $this->_source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));

        return $this;
    }

    /**
     * Returns a PHP representation of a given value.
     *
     * @param mixed $value The value to convert
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function repr($value)
    {
        if (is_int($value) || is_float($value))
        {
            if (false !== $locale = setlocale(LC_NUMERIC, 0))
                setlocale(LC_NUMERIC, 'C');

            $this->raw($value);

            if (false !== $locale)
                setlocale(LC_NUMERIC, $locale);
        }
        elseif (null === $value)
        {
            $this->raw('null');
        }
        elseif (is_bool($value))
        {
            $this->raw($value ? 'true' : 'false');
        }
        elseif (is_array($value))
        {
            $this->raw('array(');
            $first = true;
            foreach ($value as $key => $v)
            {
                if (!$first)
                    $this->raw(', ');

                $first = false;
                $this->repr($key);
                $this->raw(' => ');
                $this->repr($v);
            }

            $this->raw(')');
        }
        else
        {
            $this->string($value);
        }

        return $this;
    }

    /**
     * Adds debugging information.
     *
     * @param Apishka_Templater_Node $node The related twig node
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function addDebugInfo(Apishka_Templater_NodeAbstract $node)
    {
        if ($node->getLine() != $this->_last_line)
        {
            $this->write(sprintf("// line %d\n", $node->getLine()));

            // when mbstring.func_overload is set to 2
            // mb_substr_count() replaces substr_count()
            // but they have different signatures!
            if (((int) ini_get('mbstring.func_overload')) & 2)
            {
                // this is much slower than the "right" version
                $this->_source_line += mb_substr_count(mb_substr($this->_source, $this->_source_offset), "\n");
            }
            else
            {
                $this->_source_line += substr_count($this->_source, "\n", $this->_source_offset);
            }

            $this->_source_offset = strlen($this->_source);
            $this->_debug_info[$this->_source_line] = $node->getLine();

            $this->_last_line = $node->getLine();
        }

        return $this;
    }

    /**
     * Get debug info
     *
     * @return array
     */

    public function getDebugInfo()
    {
        ksort($this->_debug_info);

        return $this->_debug_info;
    }

    /**
     * Indents the generated code.
     *
     * @param int $step The number of indentation to add
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function indent($step = 1)
    {
        $this->_indentation += $step;

        return $this;
    }

    /**
     * Outdents the generated code.
     *
     * @param int $step The number of indentation to remove
     *
     * @throws LogicException When trying to outdent too much so the indentation would become negative
     *
     * @return Apishka_Templater_Compiler The current compiler instance
     */

    public function outdent($step = 1)
    {
        // can't outdent by more steps than the current indentation level
        if ($this->_indentation < $step)
            throw new LogicException('Unable to call outdent() as the indentation would become negative');

        $this->_indentation -= $step;

        return $this;
    }

    /**
     * Get var name
     *
     * @return string
     */

    public function getVarName()
    {
        return sprintf('__internal_%s', hash('sha256', uniqid(mt_rand(), true), false));
    }
}
