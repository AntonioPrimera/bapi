<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

trait ValidatesAttributes
{
	use HandlesExceptions;
	
	protected function throwableExceptions(): array
	{
		return [
			AuthorizationException::class,
		];
	}
	
	protected function handleException(\Exception $exception): void
	{
		if ($exception instanceof BapiValidationException)
			$exception = $this->handleBapiValidationException($exception);
		
		if ($exception)
			throw $exception;
	}
	
	protected function handleBapiValidationException(BapiValidationException $exception): \Exception|null
	{
		//if the exception contains a single validation issue, return a ValidationException with a single error
		if ($exception->validationErrors instanceof BapiValidationIssue)
			return ValidationException::withMessages([
				$exception->validationErrors->attributeName => $exception->validationErrors->errorMessage
			]);
		
		//if the exception contains a list, transform any BapiValidationIssue instances into errors
		//anything other than BapiValidationIssue instances will be ignored
		//if no BapiValidationIssue instances are found, no ValidationException will be thrown
		if (is_iterable($exception->validationErrors)) {
			$errors = [];
			foreach ($exception->validationErrors as $validationError) {
				if ($validationError instanceof BapiValidationIssue)
					$errors[$validationError->attributeName] = $validationError->errorMessage;
			}
			
			if (count($errors))
				return ValidationException::withMessages($errors);
		}
		
		//if the exception doesn't contain any BapiValidationIssue instances, return the exception as is
		return $exception;
	}
	
	
}