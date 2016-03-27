<?php

/*
 * This file is part of Twig.
 *
 * (c) 2015 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Implements a no-cache strategy.
 */
class Apishka_Templater_Cache_Null implements Apishka_Templater_CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateKey($name, $className)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($key, $content)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load($key)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($key)
    {
        return 0;
    }
}
