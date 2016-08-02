<?php

/*
 * This file is part of Twig.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka templater node expression call
 *
 * @easy-extend-base
 */

abstract class Apishka_Templater_Node_Expression_CallAbstract extends Apishka_Templater_Node_ExpressionAbstract
{
    /**
     * Compile callable
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    protected function compileCallable(Apishka_Templater_Compiler $compiler)
    {
        $callable = $this->getAttribute('callable');

        $closingParenthesis = false;
        if (is_string($callable)) {
            $compiler->raw($callable);
        } elseif (is_array($callable) && $callable[0] instanceof Apishka_Templater_ExtensionInterface) {
            $compiler->raw(sprintf('$this->env->getExtension(\'%s\')->%s', $callable[0]->getName(), $callable[1]));
        } elseif (null !== $callable) {
            $closingParenthesis = true;
            $compiler->raw(sprintf('call_user_func_array($this->env->get%s(\'%s\')->getCallable(), array', ucfirst($this->getAttribute('type')), $this->getAttribute('name')));
        } else {
            throw new LogicException(
                sprintf(
                    '%s "%s" cannot be compiled because it does not define a callable to execute. Maybe you want to change compilation with a custom node class.',
                    ucfirst($this->getAttribute('type')),
                    $this->getAttribute('name')
                )
            );
        }

        $this->compileArguments($compiler);

        if ($closingParenthesis) {
            $compiler->raw(')');
        }
    }

    /**
     * Compile arguments
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    protected function compileArguments(Apishka_Templater_Compiler $compiler)
    {
        $compiler->raw('(');

        $first = true;

        if ($this->hasAttribute('needs_environment') && $this->getAttribute('needs_environment')) {
            $compiler->raw('$this->env');
            $first = false;
        }

        if ($this->hasAttribute('needs_context') && $this->getAttribute('needs_context')) {
            if (!$first) {
                $compiler->raw(', ');
            }

            $compiler->raw('$context');
            $first = false;
        }

        if ($this->hasAttribute('arguments')) {
            foreach ($this->getAttribute('arguments') as $argument) {
                if (!$first) {
                    $compiler->raw(', ');
                }

                $compiler->string($argument);
                $first = false;
            }
        }

        if ($this->hasNode('node')) {
            if (!$first) {
                $compiler->raw(', ');
            }

            $compiler->subcompile($this->getNode('node'));
            $first = false;
        }

        if ($this->hasNode('arguments') && null !== $this->getNode('arguments')) {
            $callable = $this->getAttribute('callable');
            $arguments = $this->getArguments($callable, $this->getNode('arguments'));

            foreach ($arguments as $node) {
                if (!$first) {
                    $compiler->raw(', ');
                }

                $compiler->subcompile($node);
                $first = false;
            }
        }

        $compiler->raw(')');
    }

    /**
     * Get arguments
     *
     * @param callable $callable
     * @param array    $arguments
     */

    protected function getArguments(callable $callable = null, $arguments)
    {
        $callType = $this->getAttribute('type');
        $callName = $this->getAttribute('name');

        $parameters = array();
        $named = false;
        foreach ($arguments as $name => $node) {
            if (!is_int($name)) {
                $named = true;
                $name = $this->normalizeName($name);
            } elseif ($named) {
                throw new Apishka_Templater_Error_Syntax(sprintf('Positional arguments cannot be used after named arguments for %s "%s".', $callType, $callName));
            }

            $parameters[$name] = $node;
        }

        $isVariadic = $this->hasAttribute('is_variadic') && $this->getAttribute('is_variadic');
        if (!$named && !$isVariadic) {
            return $parameters;
        }

        if (!$callable) {
            if ($named) {
                $message = sprintf('Named arguments are not supported for %s "%s".', $callType, $callName);
            } else {
                $message = sprintf('Arbitrary positional arguments are not supported for %s "%s".', $callType, $callName);
            }

            throw new LogicException($message);
        }

        // manage named arguments
        $callableParameters = $this->getCallableParameters($callable, $isVariadic);
        $arguments = array();
        $names = array();
        $missingArguments = array();
        $optionalArguments = array();
        $pos = 0;
        foreach ($callableParameters as $callableParameter) {
            $names[] = $name = $this->normalizeName($callableParameter->name);

            if (array_key_exists($name, $parameters)) {
                if (array_key_exists($pos, $parameters)) {
                    throw new Apishka_Templater_Error_Syntax(sprintf('Argument "%s" is defined twice for %s "%s".', $name, $callType, $callName));
                }

                if (!empty($missingArguments)) {
                    throw new Apishka_Templater_Error_Syntax(
                        sprintf(
                            'Argument "%s" could not be assigned for %s "%s(%s)" because it is mapped to an internal PHP function which cannot determine default value for optional argument%s "%s".',
                            $name, $callType, $callName, implode(', ', $names), count($missingArguments) > 1 ? 's' : '', implode('", "', $missingArguments)
                        )
                    );
                }

                $arguments = array_merge($arguments, $optionalArguments);
                $arguments[] = $parameters[$name];
                unset($parameters[$name]);
                $optionalArguments = array();
            } elseif (array_key_exists($pos, $parameters)) {
                $arguments = array_merge($arguments, $optionalArguments);
                $arguments[] = $parameters[$pos];
                unset($parameters[$pos]);
                $optionalArguments = array();
                ++$pos;
            } elseif ($callableParameter->isDefaultValueAvailable()) {
                $optionalArguments[] = Apishka_Templater_Node_Expression_Constant::apishka($callableParameter->getDefaultValue(), -1);
            } elseif ($callableParameter->isOptional()) {
                if (empty($parameters)) {
                    break;
                } else {
                    $missingArguments[] = $name;
                }
            } else {
                throw new Apishka_Templater_Error_Syntax(sprintf('Value for argument "%s" is required for %s "%s".', $name, $callType, $callName));
            }
        }

        if ($isVariadic) {
            $arbitraryArguments = Apishka_Templater_Node_Expression_Array::apishka(array(), -1);
            foreach ($parameters as $key => $value) {
                if (is_int($key)) {
                    $arbitraryArguments->addElement($value);
                } else {
                    $arbitraryArguments->addElement($value, Apishka_Templater_Node_Expression_Constant::apishka($key, -1));
                }

                unset($parameters[$key]);
            }

            if ($arbitraryArguments->count()) {
                $arguments = array_merge($arguments, $optionalArguments);
                $arguments[] = $arbitraryArguments;
            }
        }

        if (!empty($parameters)) {
            $unknownParameter = null;
            foreach ($parameters as $parameter) {
                if ($parameter instanceof Apishka_Templater_NodeAbstract) {
                    $unknownParameter = $parameter;
                    break;
                }
            }

            throw new Apishka_Templater_Error_Syntax(
                sprintf(
                    'Unknown argument%s "%s" for %s "%s(%s)".',
                    count($parameters) > 1 ? 's' : '', implode('", "', array_keys($parameters)), $callType, $callName, implode(', ', $names)
                ),
                $unknownParameter ? $unknownParameter->getLine() : -1
            );
        }

        return $arguments;
    }

    /**
     * Normalize name
     *
     * @param string $name
     *
     * @return string
     */

    protected function normalizeName($name)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $name));
    }

    /**
     * Get callable parameters
     *
     * @param mixed $callable
     * @param mixed $isVariadic
     */

    private function getCallableParameters($callable, $isVariadic)
    {
        if (is_array($callable)) {
            $r = new ReflectionMethod($callable[0], $callable[1]);
        } elseif (is_object($callable) && !$callable instanceof Closure) {
            $r = new ReflectionObject($callable);
            $r = $r->getMethod('__invoke');
        } elseif (is_string($callable) && false !== strpos($callable, '::')) {
            $r = new ReflectionMethod($callable);
        } else {
            $r = new ReflectionFunction($callable);
        }

        $parameters = $r->getParameters();
        if ($this->hasNode('node')) {
            array_shift($parameters);
        }

        if ($this->hasAttribute('needs_environment') && $this->getAttribute('needs_environment')) {
            array_shift($parameters);
        }

        if ($this->hasAttribute('needs_context') && $this->getAttribute('needs_context')) {
            array_shift($parameters);
        }

        if ($this->hasAttribute('arguments') && null !== $this->getAttribute('arguments')) {
            foreach ($this->getAttribute('arguments') as $argument) {
                array_shift($parameters);
            }
        }

        if ($isVariadic) {
            $argument = end($parameters);
            if ($argument && $argument->isArray() && $argument->isDefaultValueAvailable() && array() === $argument->getDefaultValue()) {
                array_pop($parameters);
            } else {
                $callableName = $r->name;
                if ($r->getDeclaringClass()) {
                    $callableName = $r->getDeclaringClass()->name . '::' . $callableName;
                }

                throw new LogicException(sprintf('The last parameter of "%s" for %s "%s" must be an array with default value, eg. "array $arg = array()".', $callableName, $this->getAttribute('type'), $this->getAttribute('name')));
            }
        }

        return $parameters;
    }
}
