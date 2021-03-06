<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Enables usage of the deprecated Apishka_Templater_ExtensionAbstract::initRuntime() method.
 *
 * Explicitly implement this interface if you really need to implement the
 * deprecated initRuntime() method in your extensions.
 *
 *
 * @deprecated to be removed in 3.0
 */

interface Apishka_Templater_Extension_InitRuntimeInterface
{
    /**
     * Initializes the runtime environment.
     *
     * This is where you can load some file that contains filter functions for instance.
     *
     * @param Apishka_Templater_Environment $environment The current Apishka_Templater_Environment instance
     */
    public function initRuntime(Apishka_Templater_Environment $environment);
}
