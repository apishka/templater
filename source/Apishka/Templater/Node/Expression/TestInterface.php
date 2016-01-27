<?php

/**
 * Apishka templater node expression test interface
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

interface Apishka_Templater_Node_Expression_TestInterface
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames();
}
