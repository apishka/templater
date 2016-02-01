<?php

/**
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka templater node expression name
 *
 * @easy-extend-base
 *
 * @uses Apishka_Templater_Node_ExpressionAbstract
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Expression_Name extends Apishka_Templater_Node_ExpressionAbstract
{
    private $specialVars = array(
        '_self'     => '$this->getTemplateName()',
        '_context'  => '$context',
        '_charset'  => '$this->env->getCharset()',
        'globals'   => '$this->_getGlobals()',
    );

    public function __construct($name, $lineno)
    {
        parent::__construct(array(), array('name' => $name, 'is_defined_test' => false, 'ignore_strict_check' => false, 'always_defined' => false), $lineno);
    }

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $name = $this->getAttribute('name');

        $compiler->addDebugInfo($this);

        if ($this->getAttribute('is_defined_test')) {
            if ($this->isSpecial()) {
                $compiler->repr(true);
            } else {
                $compiler->raw('array_key_exists(')->repr($name)->raw(', $context)');
            }
        } elseif ($this->isSpecial()) {
            $compiler->raw($this->specialVars[$name]);
        } elseif ($this->getAttribute('always_defined')) {
            $compiler
                ->raw('$context[')
                ->string($name)
                ->raw(']')
            ;
        } else {
            if ($this->getAttribute('ignore_strict_check') || !$compiler->getEnvironment()->isStrictVariables()) {
                $compiler
                    ->raw('(isset($context[')
                    ->string($name)
                    ->raw(']) ? $context[')
                    ->string($name)
                    ->raw('] : null)')
                ;
            } else {
                // When Twig will require PHP 7.0, the Template::notFound() method
                // will be removed and the code inlined like this:
                // (function () { throw new Exception(...); })();
                $compiler
                    ->raw('(isset($context[')
                    ->string($name)
                    ->raw(']) || array_key_exists(')
                    ->string($name)
                    ->raw(', $context) ? $context[')
                    ->string($name)
                    ->raw('] : $this->notFound(')
                    ->string($name)
                    ->raw(', ')
                    ->repr($this->lineno)
                    ->raw('))')
                ;
            }
        }
    }

    public function isSpecial()
    {
        return isset($this->specialVars[$this->getAttribute('name')]);
    }

    public function isSimple()
    {
        return !$this->isSpecial() && !$this->getAttribute('is_defined_test');
    }
}
