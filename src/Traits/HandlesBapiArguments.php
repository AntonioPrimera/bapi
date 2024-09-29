<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use ReflectionMethod;

trait HandlesBapiArguments
{
    protected array $arguments = [];
	
    public function __get($key)
    {
		if (array_key_exists($key, $this->arguments))
			return $this->arguments[$key];
		
		throw new BapiException(
			"Bapi does not have an attribute '$key'."
			. " Make sure you use named attributes when calling the bapi 'run' method."
		);
    }
    
    public function __set($key, $value)
    {
		$this->arguments[$key] = $value;
    }
    
    public function __isset($key)
    {
        return isset($this->arguments[$key]);
    }
	
	/**
	 * Uses reflection to match the provided named arguments to the arguments of the 'handle' method
	 * and saves their values into the attribute set of the bapi (accessible via magic methods)
	 *
	 * @throws BapiException
	 */
	protected function fillArguments(...$args) : static
	{
		if (!method_exists($this, 'handle'))
			throw new BapiException('Bapi must have a "handle" method', 0);
		
		//run through all parameters of the handle method and set the corresponding attribute to the value of
		//the argument with the same name (or the default value, if the argument was not provided)
		foreach((new ReflectionMethod($this, 'handle'))->getParameters() as $param) {
			$paramName = $param->getName();
			$defaultValue = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
			$this->arguments[$paramName] = $args[$paramName] ?? $defaultValue;
		}
		
		return $this;
	}
}