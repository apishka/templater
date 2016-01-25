<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_IncludeTest extends Apishka_Templater_Test_NodeTestCase
{
    public function testConstructor()
    {
        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo.twig', 1);
        $node = Apishka_Templater_Node_Include::apishka($expr, null, false, false, 1);

        $this->assertNull($node->getNode('variables'));
        $this->assertEquals($expr, $node->getNode('expr'));
        $this->assertFalse($node->getAttribute('only'));

        $vars = Apishka_Templater_Node_Expression_Array::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), Apishka_Templater_Node_Expression_Constant::apishka(true, 1)), 1);
        $node = Apishka_Templater_Node_Include::apishka($expr, $vars, true, false, 1);
        $this->assertEquals($vars, $node->getNode('variables'));
        $this->assertTrue($node->getAttribute('only'));
    }

    public function getTests()
    {
        $tests = array();

        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo.twig', 1);
        $node = Apishka_Templater_Node_Include::apishka($expr, null, false, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->loadTemplate("foo.twig", null, 1)->display(\$context);
EOF
        );

        $expr = Apishka_Templater_Node_Expression_Conditional::apishka(
                        Apishka_Templater_Node_Expression_Constant::apishka(true, 1),
                        Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
                        Apishka_Templater_Node_Expression_Constant::apishka('foo', 1),
                        0
                    );
        $node = Apishka_Templater_Node_Include::apishka($expr, null, false, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->loadTemplate(((true) ? ("foo") : ("foo")), null, 1)->display(\$context);
EOF
        );

        $expr = Apishka_Templater_Node_Expression_Constant::apishka('foo.twig', 1);
        $vars = Apishka_Templater_Node_Expression_Array::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 1), Apishka_Templater_Node_Expression_Constant::apishka(true, 1)), 1);
        $node = Apishka_Templater_Node_Include::apishka($expr, $vars, false, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->loadTemplate("foo.twig", null, 1)->display(array_merge(\$context, array("foo" => true)));
EOF
        );

        $node = Apishka_Templater_Node_Include::apishka($expr, $vars, true, false, 1);
        $tests[] = array($node, <<<EOF
// line 1
\$this->loadTemplate("foo.twig", null, 1)->display(array("foo" => true));
EOF
        );

        $node = Apishka_Templater_Node_Include::apishka($expr, $vars, true, true, 1);
        $tests[] = array($node, <<<EOF
// line 1
try {
    \$this->loadTemplate("foo.twig", null, 1)->display(array("foo" => true));
} catch (Apishka_Templater_Error_Loader \$e) {
    // ignore missing template
}
EOF
        );

        return $tests;
    }
}
