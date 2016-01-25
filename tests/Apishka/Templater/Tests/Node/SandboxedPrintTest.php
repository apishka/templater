<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_SandboxedPrintTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_SandboxedPrint::apishka($expr = Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), 1);

        $this->assertEquals($expr, $node->getNode('expr'));
    }

    public function getTests()
    {
        $tests = array();

        $tests[] = array(Apishka_Templater_Node_SandboxedPrint::apishka(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), 1), <<<EOF
// line 1
echo \$this->env->getExtension('sandbox')->ensureToStringAllowed("foo");
EOF
        );

        return $tests;
    }
}
