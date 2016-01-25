<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_ImportTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $macro = Apishka_Templater_Node_Expression_Constant::apishka('foo.twig', 1);
        $var = Apishka_Templater_Node_Expression_AssignName::apishka('macro', 1);
        $node = Apishka_Templater_Node_Import::apishka($macro, $var, 1);

        $this->assertEquals($macro, $node->getNode('expr'));
        $this->assertEquals($var, $node->getNode('var'));
    }

    public function getTests()
    {
        $tests = array();

        $macro = Apishka_Templater_Node_Expression_Constant::apishka('foo.twig', 1);
        $var = Apishka_Templater_Node_Expression_AssignName::apishka('macro', 1);
        $node = Apishka_Templater_Node_Import::apishka($macro, $var, 1);

        $tests[] = array($node, <<<EOF
// line 1
\$context["macro"] = \$this->loadTemplate("foo.twig", null, 1);
EOF
        );

        return $tests;
    }
}
