<?php
/**
 * @author Denis Fohl
 */

namespace Zac2\Common;


class Field implements FieldInterface
{

    /**
     * @var mixed
     */
    protected $key;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var FilterInterface[]
     */
    protected $inputFilter;
    /**
     * @var FilterInterface[]
     */
    protected $outputFilter = array();

    /**
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->key      = (array_key_exists('key', $params))   ? $params['key'] : null;
        $this->value    = (array_key_exists('value', $params)) ? $params['value'] : null;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getFilteredValue()
    {
        $value = $this->getValue();
        foreach ($this->getOutputFilter() as $filter) {
            $value = $filter->filter($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return FilterInterface[]
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * @param FilterInterface[] $inputFilter
     */
    public function setInputFilter(array $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * @return FilterInterface[]
     */
    public function getOutputFilter()
    {
        return $this->outputFilter;
    }

    /**
     * @param FilterInterface[] $outputFilter
     */
    public function setOutputFilter(array $outputFilter)
    {
        $this->outputFilter = $outputFilter;
    }

}