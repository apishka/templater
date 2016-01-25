<?php

$config = @include 'vendor/apishka/cs/.php_cs';

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in('source/')
    ->in('tests/')
;
$config->finder($finder);

return $config;
