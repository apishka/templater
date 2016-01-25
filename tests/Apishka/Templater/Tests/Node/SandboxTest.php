<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_SandboxTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new Apishka_Templater_Node_Text('foo', 1);
        $node = new Apishka_Templater_Node_Sandbox($body, 1);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $tests = array();

        $body = new Apishka_Templater_Node_Text('foo', 1);
        $node = new Apishka_Templater_Node_Sandbox($body, 1);

        $tests[] = array($node, <<<EOF
// line 1
\$sandbox = \$this->env->getExtension('sandbox');
if (!\$alreadySandboxed = \$sandbox->isSandboxed()) {
    \$sandbox->enableSandbox();
}
echo "foo";
if (!\$alreadySandboxed) {
    \$sandbox->disableSandbox();
}
EOF
        );

        return $tests;
    }
}
