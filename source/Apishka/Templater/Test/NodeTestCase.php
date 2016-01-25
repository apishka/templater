<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class Apishka_Templater_Test_NodeTestCase extends PHPUnit_Framework_TestCase
{
    abstract public function getTests();

    /**
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null, $isPattern = false)
    {
        $this->assertNodeCompilation($source, $node, $environment, $isPattern);
    }

    public function assertNodeCompilation($source, Apishka_Templater_Node $node, Apishka_Templater_Environment $environment = null, $isPattern = false)
    {
        $compiler = $this->getCompiler($environment);
        $compiler->compile($node);

        if ($isPattern) {
            $this->assertStringMatchesFormat($source, trim($compiler->getSource()));
        } else {
            $this->assertEquals($source, trim($compiler->getSource()));
        }
    }

    protected function getCompiler(Apishka_Templater_Environment $environment = null)
    {
        return new Apishka_Templater_Compiler(null === $environment ? $this->getEnvironment() : $environment);
    }

    protected function getEnvironment()
    {
        return new Apishka_Templater_Environment(new Apishka_Templater_Loader_Array(array()));
    }

    protected function getVariableGetter($name, $line = false)
    {
        $line = $line > 0 ? "// line {$line}\n" : '';

        return sprintf('%s(isset($context["%s"]) ? $context["%s"] : null)', $line, $name, $name);
    }

    protected function getAttributeGetter()
    {
        if (function_exists('twig_template_get_attributes')) {
            return 'twig_template_get_attributes($this, ';
        }

        return '$this->getAttribute(';
    }
}
