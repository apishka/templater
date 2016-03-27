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
 */

abstract class Apishka_Templater_TemplateAbstract implements Apishka_Templater_TemplateInterface
{
    const ANY_CALL      = 'any';
    const ARRAY_CALL    = 'array';
    const METHOD_CALL   = 'method';

    /**
     * Environment
     *
     * @var mixed
     */

    protected $env;

    /**
     * Blocks
     *
     * @var array
     */

    protected $_blocks = array();

    /**
     * Constructor.
     *
     * @param Apishka_Templater_Environment $environment A Apishka_Templater_Environment instance
     */

    public function __construct(Apishka_Templater_Environment $environment)
    {
        $this->env = $environment;
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
        return $this->env->getGlobals();
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
        try {
            $this->{$this->getBlockFunctionName($name)}($context, $blocks);
        } catch (Apishka_Templater_Error $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($this->getTemplateName());
            }

            // this is mostly useful for Apishka_Templater_Error_Loader exceptions
            // see Apishka_Templater_Error_Loader
            if (false === $e->getTemplateLine()) {
                $e->setTemplateLine(-1);
                $e->guess();
            }

            throw $e;
        }
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
        return method_exists(
            $this,
            $this->getBlockFunctionName($name)
        );
    }

    /**
     * Get block function name
     *
     * @param string $name
     *
     * @return string
     */

    protected function getBlockFunctionName($name)
    {
        return 'block_' . $name;
    }

    /**
     * Load template
     *
     * @param mixed $template
     * @param mixed $templateName
     * @param mixed $line
     * @param mixed $index
     */

    protected function loadTemplate($template, $templateName = null, $line = null, $index = null)
    {
        try {
            if (is_array($template)) {
                return $this->env->resolveTemplate($template);
            }

            return $this->env->loadTemplate($template, $index);
        } catch (Apishka_Templater_Error $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($templateName ? $templateName : $this->getTemplateName());
            }

            if ($e->getTemplateLine()) {
                throw $e;
            }

            if (!$line) {
                $e->guess();
            } else {
                $e->setTemplateLine($line);
            }

            throw $e;
        }
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

        if (!file_exists($file)) {
            return;
        }

        $source = file($file, FILE_IGNORE_NEW_LINES);
        array_splice($source, 0, $reflector->getEndLine());

        $i = 0;
        while (isset($source[$i]) && '/* */' === substr_replace($source[$i], '', 3, -2)) {
            $source[$i] = str_replace('*//* ', '*/', substr($source[$i], 3, -2));
            ++$i;
        }

        array_splice($source, $i);

        return implode("\n", $source);
    }

    /**
     * {@inheritdoc}
     */

    public function render(array $context)
    {
        $level = ob_get_level();
        ob_start();
        try {
            $this->display($context);
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Returns the attribute value for a given array/object.
     *
     * @param mixed  $object    The object or array from where to get the item
     * @param mixed  $item      The item to get from the array or object
     * @param array  $arguments An array of arguments to pass if the item is an object method
     * @param string $type      The type of attribute (@see Apishka_Templater_TemplateAbstract constants)
     *
     * @return mixed
     */

    protected function getAttribute($object, $item, array $arguments = array(), $type = self::ANY_CALL)
    {
        // array
        if (self::METHOD_CALL !== $type) {
            $array_item = is_bool($item) || is_float($item)
                ? (int) $item
                : $item
            ;

            if ((is_array($object) && array_key_exists($array_item, $object)) || ($object instanceof ArrayAccess && isset($object[$array_item]))) {
                return $object[$array_item];
            }

            if (self::ARRAY_CALL === $type || !is_object($object)) {
                return;
            }
        }

        if (!is_object($object)) {
            return;
        }

        // object property
        if (self::METHOD_CALL !== $type) {
            return $object->$item;
        }

        return call_user_func_array(
            array($object, $item),
            $arguments
        );
    }
}
