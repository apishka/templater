<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Apishka_Tests_Templater_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Error
     */
    public function testDisplayBlocksAcceptTemplateOnlyAsBlocks()
    {
        $template = $this->getMockForAbstractClass('Apishka_Templater_TemplateAbstract', array(), '', false);
        $template->displayBlock('foo', array(), array('foo' => array(new stdClass(), 'foo')));
    }

    public function testGetSource()
    {
        $template = new Apishka_Templater_TemplateTest(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')), false);

        $this->assertSame("<? */*bar*/ ?>\n", $template->getSource());
    }

    /**
     * @dataProvider getGetAttributeWithTemplateAsObject
     */
    public function testGetAttributeWithTemplateAsObject($useExt)
    {
        $template = new Apishka_Templater_TemplateTest(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')), $useExt);
        $template1 = new Apishka_Templater_TemplateTest(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')), false);

        $this->assertInstanceof('Apishka_Templater_Markup', $template->getAttribute($template1, 'string'));
        $this->assertEquals('some_string', $template->getAttribute($template1, 'string'));

        $this->assertInstanceof('Apishka_Templater_Markup', $template->getAttribute($template1, 'true'));
        $this->assertEquals('1', $template->getAttribute($template1, 'true'));

        $this->assertInstanceof('Apishka_Templater_Markup', $template->getAttribute($template1, 'zero'));
        $this->assertEquals('0', $template->getAttribute($template1, 'zero'));

        $this->assertNotInstanceof('Apishka_Templater_Markup', $template->getAttribute($template1, 'empty'));
        $this->assertSame('', $template->getAttribute($template1, 'empty'));

        $this->assertFalse($template->getAttribute($template1, 'env', array(), Apishka_Templater_TemplateAbstract::ANY_CALL, true));
        $this->assertFalse($template->getAttribute($template1, 'environment', array(), Apishka_Templater_TemplateAbstract::ANY_CALL, true));
        $this->assertFalse($template->getAttribute($template1, 'getEnvironment', array(), Apishka_Templater_TemplateAbstract::METHOD_CALL, true));
        $this->assertFalse($template->getAttribute($template1, 'displayWithErrorHandling', array(), Apishka_Templater_TemplateAbstract::METHOD_CALL, true));
    }

    public function getGetAttributeWithTemplateAsObject()
    {
        $bools = array(
            array(false),
        );

        if (function_exists('twig_template_get_attributes')) {
            $bools[] = array(true);
        }

        return $bools;
    }

    /**
     * @dataProvider getTestsDependingOnExtensionAvailability
     */
    public function testGetAttributeOnArrayWithConfusableKey($useExt = false)
    {
        $template = new Apishka_Templater_TemplateTest(
            new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')),
            $useExt
        );

        $array = array('Zero', 'One', -1 => 'MinusOne', '' => 'EmptyString', '1.5' => 'FloatButString', '01' => 'IntegerButStringWithLeadingZeros');

        $this->assertSame('Zero', $array[false]);
        $this->assertSame('One', $array[true]);
        $this->assertSame('One', $array[1.5]);
        $this->assertSame('One', $array['1']);
        $this->assertSame('MinusOne', $array[-1.5]);
        $this->assertSame('FloatButString', $array['1.5']);
        $this->assertSame('IntegerButStringWithLeadingZeros', $array['01']);
        $this->assertSame('EmptyString', $array[null]);

        $this->assertSame('Zero', $template->getAttribute($array, false), 'false is treated as 0 when accessing an array (equals PHP behavior)');
        $this->assertSame('One', $template->getAttribute($array, true), 'true is treated as 1 when accessing an array (equals PHP behavior)');
        $this->assertSame('One', $template->getAttribute($array, 1.5), 'float is casted to int when accessing an array (equals PHP behavior)');
        $this->assertSame('One', $template->getAttribute($array, '1'), '"1" is treated as integer 1 when accessing an array (equals PHP behavior)');
        $this->assertSame('MinusOne', $template->getAttribute($array, -1.5), 'negative float is casted to int when accessing an array (equals PHP behavior)');
        $this->assertSame('FloatButString', $template->getAttribute($array, '1.5'), '"1.5" is treated as-is when accessing an array (equals PHP behavior)');
        $this->assertSame('IntegerButStringWithLeadingZeros', $template->getAttribute($array, '01'), '"01" is treated as-is when accessing an array (equals PHP behavior)');
        $this->assertSame('EmptyString', $template->getAttribute($array, null), 'null is treated as "" when accessing an array (equals PHP behavior)');
    }

    public function getTestsDependingOnExtensionAvailability()
    {
        if (function_exists('twig_template_get_attributes')) {
            return array(array(false), array(true));
        }

        return array(array(false));
    }

    /**
     * @dataProvider getGetAttributeTests
     */
    public function testGetAttribute($defined, $value, $object, $item, $arguments, $type, $useExt = false)
    {
        $template = new Apishka_Templater_TemplateTest(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')), $useExt);

        $this->assertEquals($value, $template->getAttribute($object, $item, $arguments, $type));
    }

    /**
     * @dataProvider getTestsDependingOnExtensionAvailability
     */
    public function testGetAttributeCallExceptions($useExt = false)
    {
        $template = new Apishka_Templater_TemplateTest(new Apishka_Templater_Environment($this->getMock('Apishka_Templater_LoaderInterface')), $useExt);

        $object = new Apishka_Templater_TemplateMagicMethodExceptionObject();

        $this->assertNull($template->getAttribute($object, 'foo'));
    }

    public function getGetAttributeTests()
    {
        $array = array(
            'defined'   => 'defined',
            'zero'      => 0,
            'null'      => null,
            '1'         => 1,
            'bar'       => true,
            '09'        => '09',
            '+4'        => '+4',
        );

        $objectArray = new Apishka_Templater_TemplateArrayAccessObject();
        $stdObject = (object) $array;
        $magicPropertyObject = new Apishka_Templater_TemplateMagicPropertyObject();
        $propertyObject = new Apishka_Templater_TemplatePropertyObject();
        $propertyObject1 = new Apishka_Templater_TemplatePropertyObjectAndIterator();
        $propertyObject2 = new Apishka_Templater_TemplatePropertyObjectAndArrayAccess();
        $propertyObject3 = new Apishka_Templater_TemplatePropertyObjectDefinedWithUndefinedValue();
        $methodObject = new Apishka_Templater_TemplateMethodObject();
        $magicMethodObject = new Apishka_Templater_TemplateMagicMethodObject();

        $anyType = Apishka_Templater_TemplateAbstract::ANY_CALL;
        $methodType = Apishka_Templater_TemplateAbstract::METHOD_CALL;
        $arrayType = Apishka_Templater_TemplateAbstract::ARRAY_CALL;

        $basicTests = array(
            // array(defined, value, property to fetch)
            array('defined', 'defined'),
            array(null,      'undefined'),
            array(null,      'protected'),
            array(0,         'zero'),
            array(1,         1),
            array(1,         1.0),
            array(null,      'null'),
            array(true,      'bar'),
            array('09',      '09'),
            array('+4',      '+4'),
        );
        $testObjects = array(
            // array(object, type of fetch)
            array($array,               $arrayType),
            array($objectArray,         $arrayType),
            array($stdObject,           $anyType),
            array($magicPropertyObject, $anyType),
            array($methodObject,        $methodType),
            array($methodObject,        $anyType),
            array($propertyObject,      $anyType),
            array($propertyObject1,     $anyType),
            array($propertyObject2,     $anyType),
        );

        $tests = array();
        foreach ($testObjects as $testObject) {
            foreach ($basicTests as $test) {
                if ($testObject[0] instanceof stdClass) {
                    if (is_numeric($test[1])) {
                        continue;
                    }

                    if (in_array($test[1], ['undefined', 'protected'])) {
                        continue;
                    }
                }

                if (($testObject[0] instanceof Apishka_Templater_TemplatePropertyObject) && is_numeric($test[1])) {
                    continue;
                }

                if ('+4' === $test[1] && $methodObject === $testObject[0]) {
                    continue;
                }

                if ($testObject[0] instanceof Apishka_Templater_TemplateMethodObject && is_numeric($test[1])) {
                    continue;
                }

                $tests[] = array($test[0], $test[0], $testObject[0], $test[1], array(), $testObject[1]);
            }
        }

        return $tests;

        // additional properties tests
        $tests = array_merge($tests, array(
            array(true, null, $propertyObject3, 'foo', array(), $anyType),
        ));

        // additional method tests
        $tests = array_merge($tests, array(
            array(true, 'defined', $methodObject, 'defined',    array(), $methodType),
            array(true, 'defined', $methodObject, 'DEFINED',    array(), $methodType),
            array(true, 'defined', $methodObject, 'getDefined', array(), $methodType),
            array(true, 'defined', $methodObject, 'GETDEFINED', array(), $methodType),
            array(true, 'static',  $methodObject, 'static',     array(), $methodType),
            array(true, 'static',  $methodObject, 'getStatic',  array(), $methodType),

            array(true, '__call_undefined', $magicMethodObject, 'undefined', array(), $methodType),
            array(true, '__call_UNDEFINED', $magicMethodObject, 'UNDEFINED', array(), $methodType),
        ));

        // add the same tests for the any type
        foreach ($tests as $test) {
            if ($anyType !== $test[5]) {
                $test[5] = $anyType;
                $tests[] = $test;
            }
        }

        $methodAndPropObject = new Apishka_Templater_TemplateMethodAndPropObject();

        // additional method tests
        $tests = array_merge($tests, array(
            array(true, 'a', $methodAndPropObject, 'a', array(), $anyType),
            array(true, 'a', $methodAndPropObject, 'a', array(), $methodType),
            array(false, null, $methodAndPropObject, 'a', array(), $arrayType),

            array(true, 'b_prop', $methodAndPropObject, 'b', array(), $anyType),
            array(true, 'b', $methodAndPropObject, 'B', array(), $anyType),
            array(true, 'b', $methodAndPropObject, 'b', array(), $methodType),
            array(true, 'b', $methodAndPropObject, 'B', array(), $methodType),
            array(false, null, $methodAndPropObject, 'b', array(), $arrayType),

            array(false, null, $methodAndPropObject, 'c', array(), $anyType),
            array(false, null, $methodAndPropObject, 'c', array(), $methodType),
            array(false, null, $methodAndPropObject, 'c', array(), $arrayType),

        ));

        // tests when input is not an array or object
        $tests = array_merge($tests, array(
            array(false, null, 42, 'a', array(), $anyType, false, 'Impossible to access an attribute ("a") on a integer variable ("42")'),
            array(false, null, 'string', 'a', array(), $anyType, false, 'Impossible to access an attribute ("a") on a string variable ("string")'),
            array(false, null, array(), 'a', array(), $anyType, false, 'Key "a" does not exist as the array is empty'),
        ));

        // add twig_template_get_attributes tests

        if (function_exists('twig_template_get_attributes')) {
            foreach (array_slice($tests, 0) as $test) {
                $test = array_pad($test, 7, null);
                $test[6] = true;
                $tests[] = $test;
            }
        }

        return $tests;
    }
}

class Apishka_Templater_TemplateTest extends Apishka_Templater_TemplateAbstract
{
    public static $cache = array();
    protected $useExtGetAttribute = false;

    public function __get($name)
    {
        if (method_exists($this, $method = 'get' . $name)) {
            return $this->$method();
        }

        return;
    }

    public function __construct(Apishka_Templater_Environment $env, $useExtGetAttribute = false)
    {
        parent::__construct($env);
        $this->useExtGetAttribute = $useExtGetAttribute;
        self::$cache = array();
    }

    public function getSupportedNames()
    {
        return array(
            'Apishka_Templater_TemplateTest',
        );
    }

    public function getZero()
    {
        return 0;
    }

    public function getEmpty()
    {
        return '';
    }

    public function getString()
    {
        return 'some_string';
    }

    public function getTrue()
    {
        return true;
    }

    public function getTemplateName()
    {
    }

    public function getDebugInfo()
    {
        return array();
    }

    protected function doGetParent(array $context)
    {
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
    }

    public function getAttribute($object, $item, array $arguments = array(), $type = Apishka_Templater_TemplateAbstract::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        if ($this->useExtGetAttribute) {
            return twig_template_get_attributes($this, $object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        } else {
            return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
        }
    }
}
/* <? *//* *bar*//*  ?>*/
/* */

class Apishka_Templater_TemplateArrayAccessObject implements ArrayAccess
{
    protected $protected = 'protected';

    public $attributes = array(
        'defined' => 'defined',
        'zero'    => 0,
        'null'    => null,
        '1'       => 1,
        'bar'     => true,
        '09'      => '09',
        '+4'      => '+4',
    );

    public function offsetExists($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function offsetGet($name)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : null;
    }

    public function offsetSet($name, $value)
    {
    }

    public function offsetUnset($name)
    {
    }
}

class Apishka_Templater_TemplateMagicPropertyObject
{
    public $defined = 'defined';

    public $attributes = array(
        'zero' => 0,
        'null' => null,
        '1'    => 1,
        'bar'  => true,
        '09'   => '09',
        '+4'   => '+4',
    );

    protected $protected = 'protected';

    public function __isset($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : null;
    }
}

class Apishka_Templater_TemplateMagicPropertyObjectWithException
{
    public function __isset($key)
    {
        throw new Exception('Hey! Don\'t try to isset me!');
    }
}

class Apishka_Templater_TemplatePropertyObject
{
    public $defined = 'defined';
    public $zero = 0;
    public $null = null;
    public $bar = true;

    protected $protected = 'protected';

    public function __get($name)
    {
        return;
    }
}

class Apishka_Templater_TemplatePropertyObjectAndIterator extends Apishka_Templater_TemplatePropertyObject implements IteratorAggregate
{
    public function getIterator()
    {
        return new ArrayIterator(array('foo', 'bar'));
    }
}

class Apishka_Templater_TemplatePropertyObjectAndArrayAccess extends Apishka_Templater_TemplatePropertyObject implements ArrayAccess
{
    private $data = array();

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : 'n/a';
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}

class Apishka_Templater_TemplatePropertyObjectDefinedWithUndefinedValue
{
    public $foo;

    public function __construct()
    {
        $this->foo = @$notExist;
    }
}

class Apishka_Templater_TemplateMethodObject
{
    public function getDefined()
    {
        return 'defined';
    }

    public function get1()
    {
        return 1;
    }

    public function get09()
    {
        return '09';
    }

    public function getZero()
    {
        return 0;
    }

    public function getNull()
    {
    }

    public function getBar()
    {
        return true;
    }

    protected function getProtected()
    {
        return;
    }

    public static function getStatic()
    {
        return 'static';
    }

    public function __get($name)
    {
        if (method_exists($this, $method = 'get' . $name)) {
            return $this->$method();
        }

        return;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $method = 'get' . $name)) {
            return call_user_func_array([$this, $method], $arguments);
        }

        return;
    }
}

class Apishka_Templater_TemplateMethodAndPropObject
{
    private $a = 'a_prop';
    public function getA()
    {
        return 'a';
    }

    public $b = 'b_prop';
    public function getB()
    {
        return 'b';
    }

    private $c = 'c_prop';
    private function getC()
    {
        return 'c';
    }
}

class Apishka_Templater_TemplateMagicMethodObject
{
    public function __call($method, $arguments)
    {
        return '__call_' . $method;
    }
}

class Apishka_Templater_TemplateMagicMethodExceptionObject
{
    public function __get($name)
    {
        return;
    }

    public function __call($method, $arguments)
    {
        throw new BadMethodCallException(sprintf('Unknown method "%s".', $method));
    }
}

class CExtDisablingNodeVisitor implements Apishka_Templater_NodeVisitorInterface
{
    public function enterNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        if ($node instanceof Apishka_Templater_Node_Expression_GetAttr) {
            $node->setAttribute('disable_c_ext', true);
        }

        return $node;
    }

    public function leaveNode(Apishka_Templater_NodeAbstract $node, Apishka_Templater_Environment $env)
    {
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
