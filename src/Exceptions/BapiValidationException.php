<?php
namespace AntonioPrimera\Bapi\Exceptions;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use Throwable;

class BapiValidationException extends BapiException
{
	public readonly mixed $validationErrors;
	
	public function __construct(mixed $validationErrors = null, $message = "", $code = 0, Throwable $previous = null)
	{
		$derivedMessage = null;
		
		//if a single validation issue was passed, use its error message
		if ($validationErrors instanceof BapiValidationIssue)
			$derivedMessage = $validationErrors->errorMessage;
		
		//if a string was passed, use it as the error message
		if (is_string($validationErrors))
			$derivedMessage = $validationErrors;
		
		parent::__construct($derivedMessage ?: $message, $code, $previous);
		
		$this->validationErrors = $validationErrors;
	}
}