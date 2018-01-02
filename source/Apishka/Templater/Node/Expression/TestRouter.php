<?php declare(strict_types = 1);

use Apishka\EasyExtend\Router\ByKeyAbstract;

/**
 * Apishka templater node expression test router
 */
class Apishka_Templater_Node_Expression_TestRouter extends ByKeyAbstract
{
    /**
     * Checks item for correct information
     *
     * @param \ReflectionClass $reflector
     *
     * @return bool
     */
    protected function isCorrectItem(\ReflectionClass $reflector): bool
    {
        return $reflector->isSubclassOf('Apishka_Templater_Node_Expression_TestInterface');
    }

    /**
     * Get class variants
     *
     * @param \ReflectionClass $reflector
     * @param object           $item
     *
     * @return array
     */
    protected function getClassVariants(\ReflectionClass $reflector, $item): array
    {
        return $item->getSupportedNames();
    }
}
