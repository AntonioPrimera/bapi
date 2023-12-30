<?php
namespace AntonioPrimera\Bapi\Traits;

use Illuminate\Support\Arr;

trait HandlesAttributes
{
    protected array $attributes = [];
	
    //--- Basic getters and setters -----------------------------------------------------------------------------------
	
	public function all() : array
	{
		return $this->attributes;
	}
	
	public function has($key) : bool
	{
		return Arr::has($this->attributes, $key);
	}
	
	public function get($key, $default = null)
	{
		return Arr::get($this->attributes, $key, $default);
	}
	
	public function set($key, $value) : static
	{
		Arr::set($this->attributes, $key, $value);
		
		return $this;
	}
    
    public function setAttributes(array $attributes) : static
    {
        $this->attributes = $attributes;
        return $this;
    }
    
    //--- Complex setters ---------------------------------------------------------------------------------------------
	
	/**
	 * Merge the given list of attributes into
	 * the current list of attributes
	 */
    public function fill(array $attributes) : static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }
    
    /**
	 * Fill in a set of given values, provided as an indexed
	 * array (numerical keys) into the corresponding
	 * attributes with the same indices
     */
    public function fillValues(array $values) : static
    {
		$valueIndex = 0;
		foreach ($this->attributes as $attributeName => $defaultValue) {
			if (isset($values[$attributeName])) {
				//named attributes will have the argument name as key
				$this->set($attributeName, $values[$attributeName]);
			} else {
				//indexed attributes must have the same index as the method argument (can not be skipped)
				$this->set($attributeName, $values[$valueIndex] ?? $defaultValue);
				$valueIndex++;
			}
		}
        
        return $this;
    }
    
    //--- Magic stuff -------------------------------------------------------------------------------------------------
    
    public function __get($key)
    {
        return $this->get($key);
    }
    
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
    
    public function __isset($key)
    {
        return !is_null($this->get($key));
    }
}