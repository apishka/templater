<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_ErrorTest extends PHPUnit_Framework_TestCase
{
    public function testErrorWithObjectFilename()
    {
        $error = new Apishka_Templater_Error('foo');
        $error->setTemplateFile(new SplFileInfo(__FILE__));

        $this->assertContains('tests' . DIRECTORY_SEPARATOR . 'Apishka' . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'Templater' . DIRECTORY_SEPARATOR . 'ErrorTest.php', $error->getMessage());
    }

    public function testErrorWithArrayFilename()
    {
        $error = new Apishka_Templater_Error('foo');
        $error->setTemplateFile(array('foo' => 'bar'));

        $this->assertEquals('foo in {"foo":"bar"}', $error->getMessage());
    }
}

class Apishka_Tests_Templater_ErrorTest_Foo
{
    public function bar()
    {
        throw new Exception('Runtime error...');
    }
}
