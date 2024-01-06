<?php

namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Exceptions\BapiValidationException;

trait HandlesValidation
{
	
	/**
	 * Do any business specific validations. If the validation is successful, return boolean true
	 * Any other result will be wrapped in a BapiValidationException and thrown immediately
	 * You may also throw a BapiValidationException directly from this method
	 */
	protected function validate() : mixed
	{
		return true;
	}
	
	/**
	 * @throws BapiValidationException
	 */
	protected function handleValidation(): void
	{
		$validationResult = $this->validate();
		
		//if anything other than boolean true was returned, throw a BapiValidationException
		if ($validationResult !== true)
			throw new BapiValidationException($validationResult);
	}
}