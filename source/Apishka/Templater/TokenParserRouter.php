<?php

/**
 * Apishka templater token parser router
 *
 * @easy-extend-base
 *
 * @uses \Apishka\EasyExtend\Router\ByClassName
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_TokenParserRouter extends \Apishka\EasyExtend\Router\ByClassName
{
    /**
     * Checks item for correct information
     *
     * @param \ReflectionClass $reflector
     *
     * @return bool
     */

    protected function isCorrectItem(\ReflectionClass $reflector)
    {
        return $reflector->isSubclassOf('Apishka_Templater_TokenParserInterface');
    }
}
