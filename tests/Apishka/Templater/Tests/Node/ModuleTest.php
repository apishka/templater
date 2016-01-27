<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Apishka_Templater_Tests_Node_ModuleTest extends Apishka_Templater_Test_NodeTestCaseAbstract
{
    public function testConstructor()
    {
        $body = Apishka_Templater_Node_Text::apishka('foo', 1);
        $parent = Apishka_Templater_Node_Expression_Constant::apishka('layout.twig', 1);
        $blocks = Apishka_Templater_Node::apishka();
        $macros = Apishka_Templater_Node::apishka();
        $traits = Apishka_Templater_Node::apishka();
        $filename = 'foo.twig';
        $node = Apishka_Templater_Node_Module::apishka($body, $parent, $blocks, $macros, $traits, Apishka_Templater_Node::apishka(array()), $filename);

        $this->assertEquals($body, $node->getNode('body'));
        $this->assertEquals($blocks, $node->getNode('blocks'));
        $this->assertEquals($macros, $node->getNode('macros'));
        $this->assertEquals($parent, $node->getNode('parent'));
        $this->assertEquals($filename, $node->getAttribute('filename'));
    }

    public function getTests()
    {
        $twig = new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface'));

        $tests = array();

        $body = Apishka_Templater_Node_Text::apishka('foo', 1);
        $extends = null;
        $blocks = Apishka_Templater_Node::apishka();
        $macros = Apishka_Templater_Node::apishka();
        $traits = Apishka_Templater_Node::apishka();
        $filename = 'foo.twig';

        $node = Apishka_Templater_Node_Module::apishka($body, $extends, $blocks, $macros, $traits, Apishka_Templater_Node::apishka(array()), $filename);
        $tests[] = array($node, <<<EOF
<?php

/* foo.twig */
class __TwigTemplate_%x extends Apishka_Templater_TemplateAbstract
{
    public function __construct(Apishka_Templater_Environment \$env)
    {
        parent::__construct(\$env);

        \$this->parent = false;

        \$this->blocks = array(
        );
    }

    protected function doDisplay(array \$context, array \$blocks = array())
    {
        // line 1
        echo "foo";
    }

    public function getTemplateName()
    {
        return "foo.twig";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }
}
EOF
        , $twig, true);

        $body = Apishka_Templater_Node::apishka(array());
        $extends = Apishka_Templater_Node_Expression_Constant::apishka('layout.twig', 1);

        $node = Apishka_Templater_Node_Module::apishka($body, $extends, $blocks, $macros, $traits, Apishka_Templater_Node::apishka(array()), $filename);
        $tests[] = array($node, <<<EOF
<?php

/* foo.twig */
class __TwigTemplate_%x extends Apishka_Templater_TemplateAbstract
{
    public function __construct(Apishka_Templater_Environment \$env)
    {
        parent::__construct(\$env);

        // line 1
        \$this->parent = \$this->loadTemplate("layout.twig", "foo.twig", 1);
        \$this->blocks = array(
        );
    }

    protected function doGetParent(array \$context)
    {
        return "layout.twig";
    }

    protected function doDisplay(array \$context, array \$blocks = array())
    {
        \$this->parent->display(\$context, array_merge(\$this->blocks, \$blocks));
    }

    public function getTemplateName()
    {
        return "foo.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  11 => 1,);
    }
}
EOF
        , $twig, true);

        $set = Apishka_Templater_Node_Set::apishka(false, Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_AssignName::apishka('foo', 4))), Apishka_Templater_Node::apishka(array(Apishka_Templater_Node_Expression_Constant::apishka('foo', 4))), 4);
        $body = Apishka_Templater_Node::apishka(array($set));
        $extends = Apishka_Templater_Node_Expression_Conditional::apishka(
                        Apishka_Templater_Node_Expression_Constant::apishka(true, 2),
                        Apishka_Templater_Node_Expression_Constant::apishka('foo', 2),
                        Apishka_Templater_Node_Expression_Constant::apishka('foo', 2),
                        2
                    );

        $node = Apishka_Templater_Node_Module::apishka($body, $extends, $blocks, $macros, $traits, Apishka_Templater_Node::apishka(array()), $filename);
        $tests[] = array($node, <<<EOF
<?php

/* foo.twig */
class __TwigTemplate_%x extends Apishka_Templater_TemplateAbstract
{
    protected function doGetParent(array \$context)
    {
        // line 2
        return \$this->loadTemplate(((true) ? ("foo") : ("foo")), "foo.twig", 2);
    }

    protected function doDisplay(array \$context, array \$blocks = array())
    {
        // line 4
        \$context["foo"] = "foo";
        // line 2
        \$this->getParent(\$context)->display(\$context, array_merge(\$this->blocks, \$blocks));
    }

    public function getTemplateName()
    {
        return "foo.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  17 => 2,  15 => 4,  9 => 2,);
    }
}
EOF
        , $twig, true);

        return $tests;
    }
}
