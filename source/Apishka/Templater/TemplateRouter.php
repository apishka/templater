<?php

/**
 * Apishka templater template router
 */

class Apishka_Templater_TemplateRouter extends \Apishka\EasyExtend\Router\ByKeyAbstract
{
    /**
     * Get item
     *
     * @param string $name
     *
     * @return mixed
     */

    public function getItem($name, ...$params)
    {
        $info = $this->getItemData($name);
        $class = $info['class'];

        return new $class(...$params);
    }

    /**
     * Checks item for correct information
     *
     * @param \ReflectionClass $reflector
     *
     * @return bool
     */

    protected function isCorrectItem(\ReflectionClass $reflector)
    {
        return $reflector->isSubclassOf('Apishka_Templater_TemplateInterface');
    }

    /**
     * Get class variants
     *
     * @param \ReflectionClass $reflector
     * @param object           $item
     *
     * @return array
     */

    protected function getClassVariants(\ReflectionClass $reflector, $item)
    {
        return $item->getSupportedNames();
    }
}
