<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Exceptions\BapiException;

trait HandlesAttributes
{
    protected array $attributes = [];
	
    public function __get($key)
    {
		if (isset($this->attributes[$key]))
			return $this->attributes[$key];
		
		throw new BapiException("Attribute $key does not exist");
    }
    
    public function __set($key, $value)
    {
		$this->attributes[$key] = $value;
    }
    
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }
}