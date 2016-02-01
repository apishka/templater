<?php

/**
 * Apishka templater node expression test
 *
 * @uses Apishka_Templater_Node_Expression_TestAbstract
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

class Apishka_Templater_Node_Expression_Test extends Apishka_Templater_Node_Expression_TestAbstract
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames()
    {
        return array(
            'base',
        );
    }
}
