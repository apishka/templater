<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Tests_Templater_Node_BlockReferenceTest extends Apishka_Tests_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $node = Apishka_Templater_Node_BlockReference::apishka('foo', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
    }

    public function getTests()
    {
        return array(
            array(Apishka_Templater_Node_BlockReference::apishka('foo', 1), <<<EOF
// line 1
\$this->displayBlock('foo', \$context, \$blocks);
EOF
            ),
        );
    }
}
