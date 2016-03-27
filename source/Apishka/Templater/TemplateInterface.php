<?php

/**
 * Apishka templater template interface
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
