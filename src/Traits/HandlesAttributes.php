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
	
	//public function only($keys)
	//{
	//    return Arr::only($this->attributes, is_array($keys) ? $keys : func_get_args());
	//}
	//
	//public function except($keys)
	//{
	//    return Arr::except($this->attributes, is_array($keys) ? $keys : func_get_args());
	//}
	
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
	 *
	 * @param array $attributes
	 *
	 * @return $this
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
     *
     * @param array $values
     *
     * @return $this
     */
    public function fillValues(array $values) : static
    {
        $valueIndex = 0;
        foreach ($this->attributes as $attribute => $defaultValue) {
            $this->set($attribute, $values[$valueIndex] ?? $defaultValue);
            $valueIndex++;
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