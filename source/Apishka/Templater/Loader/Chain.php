<?php

/*
 * This file is part of Twig.
 *
 * (c) 2011 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loads templates from other loaders.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Apishka_Templater_Loader_Chain implements Apishka_Templater_LoaderInterface, Apishka_Templater_ExistsLoaderInterface
{
    private $hasSourceCache = array();
    private $loaders = array();

    /**
     * Constructor.
     *
     * @param Apishka_Templater_LoaderInterface[] $loaders An array of loader instances
     */
    public function __construct(array $loaders = array())
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Adds a loader instance.
     *
     * @param Apishka_Templater_LoaderInterface $loader A Loader instance
     */
    public function addLoader(Apishka_Templater_LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
        $this->hasSourceCache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        $exceptions = array();
        foreach ($this->loaders as $loader) {
            if (!$loader->exists($name)) {
                continue;
            }

            try {
                return $loader->getSource($name);
            } catch (Apishka_Templater_Error_Loader $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        throw new Apishka_Templater_Error_Loader(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . implode(', ', $exceptions) . ')' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        if (isset($this->hasSourceCache[$name])) {
            return $this->hasSourceCache[$name];
        }

        foreach ($this->loaders as $loader) {
            if ($loader->exists($name)) {
                return $this->hasSourceCache[$name] = true;
            }
        }

        return $this->hasSourceCache[$name] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        $exceptions = array();
        foreach ($this->loaders as $loader) {
            if (!$loader->exists($name)) {
                continue;
            }

            try {
                return $loader->getCacheKey($name);
            } catch (Apishka_Templater_Error_Loader $e) {
                $exceptions[] = get_class($loader) . ': ' . $e->getMessage();
            }
        }

        throw new Apishka_Templater_Error_Loader(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . implode(', ', $exceptions) . ')' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        $exceptions = array();
        foreach ($this->loaders as $loader) {
            if (!$loader->exists($name)) {
                continue;
            }

            try {
                return $loader->isFresh($name, $time);
            } catch (Apishka_Templater_Error_Loader $e) {
                $exceptions[] = get_class($loader) . ': ' . $e->getMessage();
            }
        }

        throw new Apishka_Templater_Error_Loader(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' (' . implode(', ', $exceptions) . ')' : ''));
    }
}
