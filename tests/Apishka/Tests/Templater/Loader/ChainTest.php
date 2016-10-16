<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Loader_ChainTest extends PHPUnit_Framework_TestCase
{
    public function testGetSource()
    {
        $loader = new Apishka_Templater_Loader_Chain(array(
            new Apishka_Templater_Loader_Array(array('foo' => 'bar')),
            new Apishka_Templater_Loader_Array(array('foo' => 'foobar', 'bar' => 'foo')),
        ));

        $this->assertEquals('bar', $loader->getSource('foo'));
        $this->assertEquals('foo', $loader->getSource('bar'));
    }

    /**
     * @expectedException Apishka_Templater_Error_Loader
     */
    public function testGetSourceWhenTemplateDoesNotExist()
    {
        $loader = new Apishka_Templater_Loader_Chain(array());

        $loader->getSource('foo');
    }

    public function testGetCacheKey()
    {
        $loader = new Apishka_Templater_Loader_Chain(array(
            new Apishka_Templater_Loader_Array(array('foo' => 'bar')),
            new Apishka_Templater_Loader_Array(array('foo' => 'foobar', 'bar' => 'foo')),
        ));

        $this->assertEquals('bar', $loader->getCacheKey('foo'));
        $this->assertEquals('foo', $loader->getCacheKey('bar'));
    }

    /**
     * @expectedException Apishka_Templater_Error_Loader
     */
    public function testGetCacheKeyWhenTemplateDoesNotExist()
    {
        $loader = new Apishka_Templater_Loader_Chain(array());

        $loader->getCacheKey('foo');
    }

    public function testAddLoader()
    {
        $loader = new Apishka_Templater_Loader_Chain();
        $loader->addLoader(new Apishka_Templater_Loader_Array(array('foo' => 'bar')));

        $this->assertEquals('bar', $loader->getSource('foo'));
    }

    public function testExists()
    {
        $loader1 = $this->createMock('Apishka_Templater_Loader_Array', array('exists', 'getSource'), array(), '', false);
        $loader1->expects($this->once())->method('exists')->will($this->returnValue(false));
        $loader1->expects($this->never())->method('getSource');

        $loader2 = $this->createMock('Apishka_Templater_LoaderInterface');
        $loader2->expects($this->once())->method('exists')->will($this->returnValue(true));
        $loader2->expects($this->never())->method('getSource');

        $loader = new Apishka_Templater_Loader_Chain();
        $loader->addLoader($loader1);
        $loader->addLoader($loader2);

        $this->assertTrue($loader->exists('foo'));
    }
}
