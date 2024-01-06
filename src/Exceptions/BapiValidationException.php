<?php
namespace AntonioPrimera\Bapi\Exceptions;

use Throwable;

class BapiValidationException extends BapiException
{
	public readonly mixed $validationErrors;
	
	public function __construct(mixed $validationErrors = null, $message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->validationErrors = $validationErrors;
	}
}