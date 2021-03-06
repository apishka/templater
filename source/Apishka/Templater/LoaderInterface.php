<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface all loaders must implement.
 */
interface Apishka_Templater_LoaderInterface
{
    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     *
     * @throws Apishka_Templater_Error_Loader When $name is not found
     *
     * @return string The template source code
     */
    public function getSource($name);

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @throws Apishka_Templater_Error_Loader When $name is not found
     *
     * @return string The cache key
     */
    public function getCacheKey($name);

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time Timestamp of the last modification time of the
     *                     cached template
     *
     * @throws Apishka_Templater_Error_Loader When $name is not found
     *
     * @return bool true if the template is fresh, false otherwise
     */
    public function isFresh($name, $time);

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     *
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists($name);
}
