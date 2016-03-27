<?php

/**
 * Apishka templater token parser router
 *
 * @easy-extend-base
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
