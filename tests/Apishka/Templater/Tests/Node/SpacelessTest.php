<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_SpacelessTest extends Apishka_Templater_Tests_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Text::apishka('<div>   <div>   foo   </div>   </div>', 1)));
        $node = Apishka_Templater_Node_Spaceless::apishka($body, 1);

        $this->assertEquals($body, $node->getNode('body'));
    }

    public function getTests()
    {
        $body = Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Text::apishka('<div>   <div>   foo   </div>   </div>', 1)));
        $node = Apishka_Templater_Node_Spaceless::apishka($body, 1);

        return array(
            array($node, <<<EOF
// line 1
ob_start();
echo "<div>   <div>   foo   </div>   </div>";
echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
EOF
            ),
        );
    }
}
