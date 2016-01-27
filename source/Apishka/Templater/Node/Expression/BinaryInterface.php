<?php

/**
 * Apishka templater node expression binary interface
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

interface Apishka_Templater_Node_Expression_BinaryInterface
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

    /**
     * Get type name
     *
     * @return string
     */

    public function getTypeName();

    /**
     * Get associativity
     *
     * @return int
     */

    public function getAssociativity();
}
