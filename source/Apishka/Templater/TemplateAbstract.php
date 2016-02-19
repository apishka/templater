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
 * Default base class for compiled templates.
 *
 * @uses Apishka_Templater_TemplateInterface
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */

abstract class Apishka_Templater_TemplateAbstract implements Apishka_Templater_TemplateInterface
{
    const ANY_CALL      = 'any';
    const ARRAY_CALL    = 'array';
    const METHOD_CALL   = 'method';

    protected static $cache = array();

    protected $parent;
    protected $parents = array();
    protected $env;
    protected $blocks = array();
    protected $traits = array();

    /**
     * Constructor.
     *
     * @param Apishka_Templater_Environment $env A Apishka_Templater_Environment instance
     */
    public function __construct(Apishka_Templater_Environment $env)
    {
        $this->env = $env;
    }

    /**
     * Returns the template name.
     *
     * @return string The template name
     */
    abstract public function getTemplateName();

    /**
     * Get globals
     *
     * @return Apishka_Templater_Template_Globals
     */

    protected function _getGlobals()
    {
        if ($this->_globals === null)
            $this->_globals = Apishka_Templater_Template_Globals::apishka();

        return $this->_globals;
    }

    /**
     * Returns the parent template.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @param array $context
     *
     * @return Apishka_Templater_TemplateAbstract|false The parent template or false if there is no parent
     *
     * @internal
     */

    public function getParent(array $context)
    {
        if (null !== $this->parent)
        {
            return $this->parent;
        }

        try
        {
            $parent = $this->doGetParent($context);

            if (false === $parent)
                return false;

            if (!isset($this->parents[$parent]))
            {
                $this->parents[$parent] = $this->loadTemplate($parent);
            }
        }
        catch (Apishka_Templater_Error_Loader $e)
        {
            $e->setTemplateFile(null);
            $e->guess();

            throw $e;
        }

        return $this->parents[$parent];
    }

    /**
     * Do get parent
     *
     * @param array $context
     *
     * @return bool
     */

    protected function doGetParent(array $context)
    {
        return false;
    }

    /**
     * Is traitable
     *
     * @return bool
     */

    public function isTraitable()
    {
        return true;
    }

    /**
     * Displays a parent block.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @param string $name    The block name to display from the parent
     * @param array  $context The context
     * @param array  $blocks  The current set of blocks
     *
     * @internal
     */

    public function displayParentBlock($name, array $context, array $blocks = array())
    {
        if (isset($this->traits[$name]))
        {
            $this->traits[$name][0]->displayBlock($name, $context, $blocks, false);
        }
        elseif (false !== $parent = $this->getParent($context))
        {
            $parent->displayBlock($name, $context, $blocks, false);
        }
        else
        {
            throw new Apishka_Templater_Error_Runtime(sprintf('The template has no parent and no traits defining the "%s" block', $name), -1, $this->getTemplateName());
        }
    }

    /**
     * Displays a block.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @param string $name      The block name to display
     * @param array  $context   The context
     * @param array  $blocks    The current set of blocks
     * @param bool   $useBlocks Whether to use the current set of blocks
     *
     * @internal
     */

    public function displayBlock($name, array $context, array $blocks = array(), $useBlocks = true)
    {
        if ($useBlocks && isset($blocks[$name]))
        {
            $template = $blocks[$name][0];
            $block = $blocks[$name][1];
        }
        elseif (isset($this->blocks[$name]))
        {
            $template = $this->blocks[$name][0];
            $block = $this->blocks[$name][1];
        }
        else
        {
            $template = null;
            $block = null;
        }

        if (null !== $template)
        {
            try
            {
                $template->$block($context, $blocks);
            }
            catch (Apishka_Templater_Error $e)
            {
                if (!$e->getTemplateFile())
                    $e->setTemplateFile($template->getTemplateName());

                // this is mostly useful for Apishka_Templater_Error_Loader exceptions
                // see Apishka_Templater_Error_Loader
                if (false === $e->getTemplateLine())
                {
                    $e->setTemplateLine(-1);
                    $e->guess();
                }

                throw $e;
            }
            catch (Exception $e)
            {
                throw new Apishka_Templater_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $template->getTemplateName(), $e);
            }
        }
        elseif (false !== $parent = $this->getParent($context))
        {
            $parent->displayBlock($name, $context, array_merge($this->blocks, $blocks), false);
        }
    }

    /**
     * Renders a parent block.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @param string $name    The block name to render from the parent
     * @param array  $context The context
     * @param array  $blocks  The current set of blocks
     *
     * @return string The rendered block
     *
     * @internal
     */

    public function renderParentBlock($name, array $context, array $blocks = array())
    {
        ob_start();
        $this->displayParentBlock($name, $context, $blocks);

        return ob_get_clean();
    }

    /**
     * Renders a block.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @param string $name      The block name to render
     * @param array  $context   The context
     * @param array  $blocks    The current set of blocks
     * @param bool   $useBlocks Whether to use the current set of blocks
     *
     * @return string The rendered block
     *
     * @internal
     */

    public function renderBlock($name, array $context, array $blocks = array(), $useBlocks = true)
    {
        ob_start();
        $this->displayBlock($name, $context, $blocks, $useBlocks);

        return ob_get_clean();
    }

    /**
     * Returns whether a block exists or not.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * This method does only return blocks defined in the current template
     * or defined in "used" traits.
     *
     * It does not return blocks from parent templates as the parent
     * template name can be dynamic, which is only known based on the
     * current context.
     *
     * @param string $name The block name
     *
     * @return bool true if the block exists, false otherwise
     *
     * @internal
     */

    public function hasBlock($name)
    {
        return isset($this->blocks[$name]);
    }

    /**
     * Returns all block names.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @return array An array of block names
     *
     * @see hasBlock
     *
     * @internal
     */

    public function getBlockNames()
    {
        return array_keys($this->blocks);
    }

    /**
     * Load template
     *
     * @param mixed $template
     * @param mixed $templateName
     * @param mixed $line
     * @param mixed $index
     *
     * @return void
     */

    protected function loadTemplate($template, $templateName = null, $line = null, $index = null)
    {
        try
        {
            if (is_array($template))
                return $this->env->resolveTemplate($template);

            return $this->env->loadTemplate($template, $index);
        }
        catch (Apishka_Templater_Error $e)
        {
            if (!$e->getTemplateFile())
                $e->setTemplateFile($templateName ? $templateName : $this->getTemplateName());

            if ($e->getTemplateLine())
                throw $e;

            if (!$line)
            {
                $e->guess();
            }
            else
            {
                $e->setTemplateLine($line);
            }

            throw $e;
        }
    }

    /**
     * Returns all blocks.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * @return array An array of blocks
     *
     * @see hasBlock
     *
     * @internal
     */

    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Returns the template source code.
     *
     * @return string|null The template source code or null if it is not available
     */

    public function getSource()
    {
        $reflector = new ReflectionClass($this);
        $file = $reflector->getFileName();

        if (!file_exists($file))
            return;

        $source = file($file, FILE_IGNORE_NEW_LINES);
        array_splice($source, 0, $reflector->getEndLine());

        $i = 0;
        while (isset($source[$i]) && '/* */' === substr_replace($source[$i], '', 3, -2))
        {
            $source[$i] = str_replace('*//* ', '*/', substr($source[$i], 3, -2));
            ++$i;
        }

        array_splice($source, $i);

        return implode("\n", $source);
    }

    /**
     * {@inheritdoc}
     */

    public function display(array $context, array $blocks = array())
    {
        $this->displayWithErrorHandling(
            $this->env->mergeGlobals($context),
            array_merge($this->blocks, $blocks)
        );
    }

    /**
     * {@inheritdoc}
     */

    public function render(array $context)
    {
        $level = ob_get_level();
        ob_start();
        try
        {
            $this->display($context);
        }
        catch (Exception $e)
        {
            while (ob_get_level() > $level)
                ob_end_clean();

            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Display with error handling
     *
     * @param array $context
     * @param array $blocks
     */

    protected function displayWithErrorHandling(array $context, array $blocks = array())
    {
        try
        {
            $this->doDisplay($context, $blocks);
        }
        catch (Apishka_Templater_Error $e)
        {
            if (!$e->getTemplateFile())
                $e->setTemplateFile($this->getTemplateName());

            // this is mostly useful for Apishka_Templater_Error_Loader exceptions
            // see Apishka_Templater_Error_Loader
            if (false === $e->getTemplateLine())
            {
                $e->setTemplateLine(-1);
                $e->guess();
            }

            throw $e;
        }
        catch (Exception $e)
        {
            throw new Apishka_Templater_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $this->getTemplateName(), $e);
        }
    }

    /**
     * Auto-generated method to display the template with the given context.
     *
     * @param array $context An array of parameters to pass to the template
     * @param array $blocks  An array of blocks to pass to the template
     */

    abstract protected function doDisplay(array $context, array $blocks = array());

    /**
     * Throws an exception for an unknown variable.
     *
     * This method is for internal use only and should never be called
     * directly.
     *
     * This is an implementation detail due to a PHP limitation before version 7.0.
     *
     * @throws Apishka_Templater_Error_Runtime if the variable does not exist and Twig is running in strict mode
     *
     * @return mixed The content of the context variable
     *
     * @internal
     */

    final protected function notFound($name, $line)
    {
        throw new Apishka_Templater_Error_Runtime(sprintf('Variable "%s" does not exist', $name), $line, $this->getTemplateName());
    }

    /**
     * Returns the attribute value for a given array/object.
     *
     * @param mixed  $object              The object or array from where to get the item
     * @param mixed  $item                The item to get from the array or object
     * @param array  $arguments           An array of arguments to pass if the item is an object method
     * @param string $type                The type of attribute (@see Apishka_Templater_TemplateAbstract constants)
     * @param bool   $is_defined_test     Whether this is only a defined check
     * @param bool   $ignore_string_check Whether to ignore the strict attribute check or not
     *
     * @return mixed The attribute value, or a Boolean when $is_defined_test is true, or null when the attribute is not set and $ignore_string_check is true
     */

    protected function getAttribute($object, $item, array $arguments = array(), $type = self::ANY_CALL, $is_defined_test = false, $ignore_string_check = false)
    {
        // array
        if (self::METHOD_CALL !== $type)
        {
            $array_item = is_bool($item) || is_float($item)
                ? (int) $item
                : $item
            ;

            if ((is_array($object) && array_key_exists($array_item, $object)) || ($object instanceof ArrayAccess && isset($object[$array_item])))
            {
                if ($is_defined_test)
                    return true;

                return $object[$array_item];
            }

            if (self::ARRAY_CALL === $type || !is_object($object))
            {
                if ($is_defined_test)
                    return false;

                return;
            }
        }

        if (!is_object($object))
        {
            if ($is_defined_test)
                return false;

            return;
        }

        // object property
        if (self::METHOD_CALL !== $type)
        {
            // Apishka_Templater_TemplateAbstract does not have public properties, and we don't want to allow access to internal ones
            if ($is_defined_test)
                return true;

            return $object->$item;
        }

        return call_user_func_array(
            array($object, $item),
            $arguments
        );
    }
}
