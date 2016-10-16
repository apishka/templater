<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_EnvironmentTest extends PHPUnit_Framework_TestCase
{
    public function testCompileSourceInlinesSource()
    {
        $twig = new Apishka_Templater_Environment($this->createMock('Apishka_Templater_LoaderInterface'));

        $source = "<? */*foo*/ ?>\r\nbar\n";
        $expected = "/* <? *//* *foo*//*  ?>*/\n/* bar*/\n/* */\n";
        $compiled = $twig->compileSource($source, 'index');

        $this->assertContains($expected, $compiled);
        $this->assertNotContains('/**', $compiled);
    }

    public function testAutoReloadCacheMiss()
    {
        $templateName = __FUNCTION__;
        $templateContent = __FUNCTION__;

        $cache = $this->createMock('Apishka_Templater_CacheInterface');
        $loader = $this->getMockLoader($templateName, $templateContent);
        $twig = new Apishka_Templater_Environment($loader, array('cache' => $cache, 'auto_reload' => true, 'debug' => false));

        // Cache miss: getTimestamp returns 0 and as a result the load() is
        // skipped.
        $cache->expects($this->once())
            ->method('generateKey')
            ->will($this->returnValue('key'));
        $cache->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue(0));
        $loader->expects($this->never())
            ->method('isFresh');
        $cache->expects($this->never())
            ->method('load');

        $twig->loadTemplate($templateName);
    }

    public function testAutoReloadCacheHit()
    {
        $templateName = __FUNCTION__;
        $templateContent = __FUNCTION__;

        $cache = $this->createMock('Apishka_Templater_CacheInterface');
        $loader = $this->getMockLoader($templateName, $templateContent);
        $twig = new Apishka_Templater_Environment($loader, array('cache' => $cache, 'auto_reload' => true, 'debug' => false));

        $now = time();

        // Cache hit: getTimestamp returns something > extension timestamps and
        // the loader returns true for isFresh().
        $cache->expects($this->once())
            ->method('generateKey')
            ->will($this->returnValue('key'));
        $cache->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($now));
        $loader->expects($this->once())
            ->method('isFresh')
            ->will($this->returnValue(true));
        $cache->expects($this->once())
            ->method('load');

        $twig->loadTemplate($templateName);
    }

    public function testAutoReloadOutdatedCacheHit()
    {
        $templateName = __FUNCTION__;
        $templateContent = __FUNCTION__;

        $cache = $this->createMock('Apishka_Templater_CacheInterface');
        $loader = $this->getMockLoader($templateName, $templateContent);
        $twig = new Apishka_Templater_Environment($loader, array('cache' => $cache, 'auto_reload' => true, 'debug' => false));

        $now = time();

        $cache->expects($this->once())
            ->method('generateKey')
            ->will($this->returnValue('key'));
        $cache->expects($this->once())
            ->method('getTimestamp')
            ->will($this->returnValue($now));
        $loader->expects($this->once())
            ->method('isFresh')
            ->will($this->returnValue(false));
        $cache->expects($this->never())
            ->method('load');

        $twig->loadTemplate($templateName);
    }

    public function testAddExtension()
    {
        $twig = new Apishka_Templater_Environment($this->createMock('Apishka_Templater_LoaderInterface'));
        $twig->addExtension(new Apishka_Tests_Templater_EnvironmentTest_Extension());

        $this->assertArrayHasKey('test', $twig->getTags());
        $this->assertArrayHasKey('foo_filter', $twig->getFilters());
        $this->assertArrayHasKey('foo_function', $twig->getFunctions());
        $this->assertArrayHasKey('foo_test', $twig->getTests());
        $this->assertArrayHasKey('foo_unary', $twig->getUnaryOperators());
        $this->assertArrayHasKey('foo_binary', $twig->getBinaryOperators());
        $visitors = $twig->getNodeVisitors();
        $this->assertEquals('Apishka_Tests_Templater_EnvironmentTest_NodeVisitor', get_class($visitors[2]));
    }

    public function testAddMockExtension()
    {
        $extension = $this->createMock('Apishka_Templater_ExtensionInterface');
        $extension->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('mock'));

        $loader = new Apishka_Templater_Loader_Array(array('page' => 'hey'));

        $twig = new Apishka_Templater_Environment($loader);
        $twig->addExtension($extension);

        $this->assertInstanceOf('Apishka_Templater_ExtensionInterface', $twig->getExtension('mock'));
        $this->assertTrue($twig->isTemplateFresh('page', time()));
    }

    public function testInitRuntimeWithAnExtensionUsingInitRuntimeNoDeprecation()
    {
        $twig = new Apishka_Templater_Environment($this->createMock('Apishka_Templater_LoaderInterface'));
        $twig->addExtension(new Apishka_Tests_Templater_EnvironmentTest_ExtensionWithoutDeprecationInitRuntime());

        $twig->initRuntime();
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Unable to register extension "environment_test" as it is already registered.
     */
    public function testOverrideExtension()
    {
        $twig = new Apishka_Templater_Environment($this->createMock('Apishka_Templater_LoaderInterface'));
        $twig->addExtension(new Apishka_Tests_Templater_EnvironmentTest_Extension());
        $twig->addExtension(new Apishka_Tests_Templater_EnvironmentTest_Extension());
    }

    protected function getMockLoader($templateName, $templateContent)
    {
        $loader = $this->createMock('Apishka_Templater_LoaderInterface');
        $loader->expects($this->any())
          ->method('getSource')
          ->with($templateName)
          ->will($this->returnValue($templateContent));
        $loader->expects($this->any())
          ->method('getCacheKey')
          ->with($templateName)
          ->will($this->returnValue($templateName));

        return $loader;
    }
}

class Apishka_Tests_Templater_EnvironmentTest_Extension_WithGlobals extends Apishka_Templater_ExtensionAbstract
{
    public function getGlobals()
    {
        return array(
            'foo_global' => 'foo_global',
        );
    }

    public function getName()
    {
        return 'environment_test';
    }
}

class Apishka_Tests_Templater_EnvironmentTest_Extension extends Apishka_Templater_ExtensionAbstract implements Apishka_Templater_Extension_GlobalsInterface
{
    public function getTokenParsers()
    {
        return array(
            new Apishka_Tests_Templater_EnvironmentTest_TokenParser(),
        );
    }

    public function getNodeVisitors()
    {
        return array(
            new Apishka_Tests_Templater_EnvironmentTest_NodeVisitor(),
        );
    }

    public function getFilters()
    {
        return array(
            new Apishka_Templater_Filter('foo_filter'),
        );
    }

    public function getTests()
    {
        return array(
            Apishka_Templater_Test::apishka('foo_test'),
        );
    }

    public function getFunctions()
    {
        return array(
            new Apishka_Templater_Function('foo_function'),
        );
    }

    public function getOperators()
    {
        return array(
            array('foo_unary' => array()),
            array('foo_binary' => array()),
        );
    }

    public function getGlobals()
    {
        return array(
            'foo_global' => 'foo_global',
        );
    }

    public function getName()
    {
        return 'environment_test';
    }
}

class Apishka_Tests_Templater_EnvironmentTest_TokenParser extends Apishka_Templater_TokenParserAbstract
{
    public function parse(Apishka_Templater_Token $token)
    {
    }

    public function getTag()
    {
        return 'test';
    }
}

class Apishka_Tests_Templater_EnvironmentTest_NodeVisitor implements Apishka_Templater_NodeVisitorInterface
{
    public function enterNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        return $node;
    }

    public function leaveNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}

class Apishka_Tests_Templater_EnvironmentTest_ExtensionWithDeprecationInitRuntime extends Apishka_Templater_ExtensionAbstract
{
    public function initRuntime(Apishka_Templater_Environment $env)
    {
    }

    public function getName()
    {
        return 'with_deprecation';
    }
}

class Apishka_Tests_Templater_EnvironmentTest_ExtensionWithoutDeprecationInitRuntime extends Apishka_Templater_ExtensionAbstract implements Apishka_Templater_Extension_InitRuntimeInterface
{
    public function initRuntime(Apishka_Templater_Environment $env)
    {
    }

    public function getName()
    {
        return 'without_deprecation';
    }
}
