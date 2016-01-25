<?php

/*
 * This file is part of Twig.
 *
 * (c) 2012 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Apishka templater extension string loader
 *
 * @uses Apishka_Templater_ExtensionAbstract
 *
 * @author Evgeny Reykh <evgeny@reykh.com>
 */

class Apishka_Templater_Extension_StringLoader extends Apishka_Templater_ExtensionAbstract
{
    public function getFunctions()
    {
        return array(
            new Apishka_Templater_Function('template_from_string', 'twig_template_from_string', array('needs_environment' => true)),
        );
    }

    public function getName()
    {
        return 'string_loader';
    }
}

/**
 * Loads a template from a string.
 *
 * <pre>
 * {{ include(template_from_string("Hello {{ name }}")) }}
 * </pre>
 *
 * @param Apishka_Templater_Environment $env      A Apishka_Templater_Environment instance
 * @param string                        $template A template as a string or object implementing __toString()
 *
 * @return Apishka_Templater_Template A Apishka_Templater_Template instance
 */
function twig_template_from_string(Apishka_Templater_Environment $env, $template)
{
    return $env->createTemplate((string) $template);
}
