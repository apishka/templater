<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Loader_ArrayTest extends PHPUnit_Framework_TestCase
{
    public function testGetSource()
    {
        $loader = new Apishka_Templater_Loader_Array(array('foo' => 'bar'));

        $this->assertEquals('bar', $loader->getSource('foo'));
    }

    /**
     * @expectedException Apishka_Templater_Error_Loader
     */
    public function testGetSourceWhenTemplateDoesNotExist()
    {
        $loader = new Apishka_Templater_Loader_Array(array());

        $loader->getSource('foo');
    }

    public function testGetCacheKey()
    {
        $loader = new Apishka_Templater_Loader_Array(array('foo' => 'bar'));

        $this->assertEquals('bar', $loader->getCacheKey('foo'));
    }

    /**
     * @expectedException Apishka_Templater_Error_Loader
     */
    public function testGetCacheKeyWhenTemplateDoesNotExist()
    {
        $loader = new Apishka_Templater_Loader_Array(array());

        $loader->getCacheKey('foo');
    }

    public function testSetTemplate()
    {
        $loader = new Apishka_Templater_Loader_Array(array());
        $loader->setTemplate('foo', 'bar');

        $this->assertEquals('bar', $loader->getSource('foo'));
    }

    public function testIsFresh()
    {
        $loader = new Apishka_Templater_Loader_Array(array('foo' => 'bar'));
        $this->assertTrue($loader->isFresh('foo', time()));
    }

    /**
     * @expectedException Apishka_Templater_Error_Loader
     */
    public function testIsFreshWhenTemplateDoesNotExist()
    {
        $loader = new Apishka_Templater_Loader_Array(array());

        $loader->isFresh('foo', time());
    }
}
