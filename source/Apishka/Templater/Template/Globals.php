<?php

/**
 * Apishka templater template globals
 */

class Apishka_Templater_Template_Globals
{
    /**
     * Traits
     */

    use \Apishka\EasyExtend\Helper\ByClassNameTrait;

    /**
     * Var for store data of storage
     *
     * @var array
     */

    protected $_data = array();

    /**
     * Construct
     */

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize
     */

    public function initialize()
    {
    }

    /**
     * Returns full storage data
     *
     * @return array
     */

    public function getData()
    {
        return $this->_data;
    }

    /**
     * Returns key value
     *
     * @param string $name
     *
     * @return mixed
     */

    public function get($name)
    {
        return $this->_data[$name];
    }

    /**
     * Sets value of key
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Jihad_Modules_CoreStorage_Local_CommonAbstract
     */

    public function set($name, $value)
    {
        $this->_data[$name] = $value;

        return $this;
    }

    /**
     * Returns true if key exists
     *
     * @param string $name
     *
     * @return bool
     */

    public function has($name)
    {
        return array_key_exists($name, $this->_data);
    }

    /**
     * Deletes key
     *
     * @param string $name
     *
     * @return Jihad_Modules_CoreStorage_Local_CommonAbstract
     */

    public function del($name)
    {
        unset($this->_data[$name]);

        return $this;
    }

    /**
     * Flush data
     *
     * @return Jihad_Modules_CoreStorage_Local_CommonAbstract
     */

    public function flush()
    {
        $this->_data = array();

        return $this;
    }

    /**
     * Начать итерацию с начала.
     */

    public function rewind()
    {
        reset($this->_data);
    }

    /**
     * !__get
     *
     * @param string $name
     *
     * @return mixed
     */

    public function __get($name)
    {
        if (method_exists($this, $method = '__get' . $name)) {
            return $this->$method();
        }

        return $this->get($name);
    }

    /**
     * Magic function for set value
     *
     * @param string $name
     * @param mixed  $value
     */

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Magic function to get set flag
     *
     * @param string $name
     *
     * @return bool
     */

    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Magic function to delete key
     *
     * @param string $name
     */

    public function __unset($name)
    {
        $this->del($name);
    }
}
