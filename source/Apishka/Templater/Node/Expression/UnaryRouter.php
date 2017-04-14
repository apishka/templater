<?php

/**
 * Apishka templater node expression unary router
 */

class Apishka_Templater_Node_Expression_UnaryRouter extends \Apishka\EasyExtend\Router\ByKeyAbstract
{
    /**
     * Checks item for correct information
     *
     * @param \ReflectionClass $reflector
     *
     * @return bool
     */

    protected function isCorrectItem(\ReflectionClass $reflector)
    {
        return $reflector->isSubclassOf('Apishka_Templater_Node_Expression_UnaryInterface');
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

    /**
     * Get class data
     *
     * @param \ReflectionClass $reflector
     * @param mixed            $item
     * @param mixed            $variant
     *
     * @return array
     */

    protected function getClassData(\ReflectionClass $reflector, $item, $variant)
    {
        $data = parent::getClassData($reflector, $item, $variant);

        $data['precedence'] = $item->getPrecedence();

        return $data;
    }
}
