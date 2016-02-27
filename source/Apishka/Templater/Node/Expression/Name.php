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
        parent::__construct(array(), array('name' => $name, 'always_defined' => false), $lineno);
    }

    /**
     * Compile
     *
     * @param Apishka_Templater_Compiler $compiler
     */

    public function compile(Apishka_Templater_Compiler $compiler)
    {
        $name = $this->getAttribute('name');

        $compiler->addDebugInfo($this);

        if ($this->isSpecial())
        {
            $compiler->raw($this->specialVars[$name]);
        }
        elseif ($this->getAttribute('always_defined'))
        {
            $compiler
                ->raw('$context[')
                ->string($name)
                ->raw(']')
            ;
        }
        else
        {
            $compiler
                ->raw('(isset($context[')
                ->string($name)
                ->raw(']) ? $context[')
                ->string($name)
                ->raw('] : null)')
            ;
        }
    }

    /**
     * Is special
     *
     * @return bool
     */

    public function isSpecial()
    {
        return isset($this->specialVars[$this->getAttribute('name')]);
    }
}
