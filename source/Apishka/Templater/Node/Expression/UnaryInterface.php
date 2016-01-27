<?php

/**
 * Apishka templater node expression unary interface
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

interface Apishka_Templater_Node_Expression_UnaryInterface
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames();

    /**
     * Get precedence
     *
     * @return int
     */

    public function getPrecedence();
}
