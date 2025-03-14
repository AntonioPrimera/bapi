<?php
namespace AntonioPrimera\Bapi;

abstract class InternalBapi extends BaseBapi
{
	/**
	 * Call an internal bapi using the static "call" method, from within another bapi
	 * In comparison to a normal Bapi, an internal bapi will run without a
	 * DB transaction, authorization check and exception handling
	 */
	protected static function call(...$args): mixed
	{
		return (new static)->handleInternalRun(...$args);
	}
	
	/**
	 * Handle running the bapi, when called by another bapi (internal run)
	 * No DB transaction is used and no authorization check is done
	 */
	protected function handleInternalRun(...$args): mixed
	{
		//take the attributes from the "run" method (static / instance) and match them with
		//the arguments of the "handle" method. The "run" method must use named arguments
		$this->fillArguments(...$args);
		
		//run the business validation check
		$this->handleValidation();
		
		//call the "handle" method, which should contain the actual business logic of the bapi
		$result = $this->callMethod('handle', ...$args);
		
		//run the result through the "afterHandle" hook, for any necessary data transformation or cleanup
		return $this->processResult($result);										  //Hook: processResult
	}
}