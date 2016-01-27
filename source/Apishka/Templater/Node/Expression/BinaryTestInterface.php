<?php

/**
 * Apishka templater node expression binary test interface
 *
 * @uses Apishka_Templater_Node_Expression_BinaryInterface
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

interface Apishka_Templater_Node_Expression_BinaryTestInterface extends Apishka_Templater_Node_Expression_BinaryInterface
{
    /**
     * Parse test expression
     *
     * @return Apishka_Templater_NodeAbstract
     */

    public function parseTestExpression();
}
