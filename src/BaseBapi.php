<?php
namespace AntonioPrimera\Bapi;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use AntonioPrimera\Bapi\Traits\HandlesBapiArguments;
use AntonioPrimera\Bapi\Traits\HandlesMethodCalls;
use AntonioPrimera\Bapi\Traits\HandlesValidation;
use AntonioPrimera\Bapi\Traits\UsesDbTransactions;

abstract class BaseBapi
{
	use HandlesMethodCalls, HandlesBapiArguments,
		HandlesValidation, UsesDbTransactions;
	
	/**
	 * @throws BapiException
	 */
	public function __construct()
	{
		//make sure the bapi has a "handle" method
		if (!method_exists($this, 'handle'))
			throw new BapiException('Bapi must have a "handle" method', 0);
	}
	
	/**
	 * Use this method when calling a bapi internally from another bapi,
	 * in order to skip DB Transaction and the authorization check.
	 */
	protected static function call(...$args): mixed
	{
		return (new static)->handleInternalRun(...$args);
	}
	
	protected abstract function handleInternalRun(...$args): mixed;
	
	//--- Hooks -------------------------------------------------------------------------------------------------------
	
	/**
	 * This hook receives the data resulted from the "handle" method, right before
	 * it is returned to the context where the bapi was called. Use this if
	 * any cleanup or transformation of the data needs to be done
	 */
	protected function processResult(mixed $result) : mixed
	{
		return $result;
	}
}