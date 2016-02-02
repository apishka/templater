<?php

/**
 * Apishka templater template interface
 *
 * @author Alexander "grevus" Lobtsov <alex@lobtsov.com>
 */

interface Apishka_Templater_TemplateInterface
{
    /**
     * Get supported names
     *
     * @return array
     */

    public function getSupportedNames();
}
