<?php

namespace AntonioPrimera\Bapi\Exceptions;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Throwable;

/**
 * @property Collection $validationErrors
 */
class BapiValidationException extends BapiException
{
	
	protected Collection $validationErrors;
	
	public function __construct(
		BapiValidationIssue|iterable|null $validationErrors = null,
										  $message = "",
										  $code = 0,
		Throwable                         $previous = null
	)
	{
		parent::__construct($message, $code, $previous);
		
		$this->validationErrors = $validationErrors instanceof BapiValidationIssue
			? collect([$validationErrors])
			: collect($validationErrors);
	}
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __get(string $name)
	{
		if ($name === 'validationErrors')
			return $this->validationErrors;
		
		return null;
	}
	
	//--- Setters -----------------------------------------------------------------------------------------------------
	
	public function setValidationErrors(Collection | BapiValidationIssue $validationErrors)
	{
		$this->validationErrors = collect();
		
		//make sure we have a list of validation errors
		$validationErrorList = $validationErrors instanceof BapiValidationIssue
			? [$validationErrors]
			: $validationErrors;
		
		//only add BapiValidationError instances to the list of validation errors
		foreach ($validationErrorList as $validationError) {
			if ($validationError instanceof BapiValidationIssue)
				$this->validationErrors->push($validationError);
		}
	}
}