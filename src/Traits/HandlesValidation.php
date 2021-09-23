<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;

trait HandlesValidation
{
	
	/**
	 * Do any manual validations and throw a BapiValidationException or just return false (a BapiValidationException
	 * will be thrown if this method returns false). Return true if the validation is successful. Data arriving
	 * here should already be pre-validated, so this method is more for business specific validations.
	 *
	 * @return bool|iterable|BapiValidationIssue
	 */
    protected function validate() : bool | iterable | BapiValidationIssue
	{
        return true;
    }
    
    /**
     * Prepare the data for the Bapi
     */
    protected function prepareData() : void
    {
    }
	
	/**
	 * @throws BapiValidationException
	 */
	protected function handleValidationAndDataPreparation() : void
    {
    	$validationResult = $this->validate();
    	
    	//boolean false indicates a validation issue, without giving any details about it
		//so just throw a simple BapiValidationException
    	if ($validationResult === false)
			throw new BapiValidationException();
    	
    	//if a single BapiValidationIssue was returned, trow an exception
    	if ($validationResult instanceof BapiValidationIssue)
			throw new BapiValidationException($validationResult);
    	
    	//if a non-empty array was returned, throw an exception
		//the array should contain BapiValidationIssue instances
    	if (is_array($validationResult) && $validationResult)
    		throw new BapiValidationException($validationResult);
    	
    	//if non-empty traversable list was returned, throw an exception
		//the list should contain BapiValidationIssue instances
    	if (($validationResult instanceof \Traversable) && iterator_count($validationResult))
			throw new BapiValidationException($validationResult);
    	
        //if boolean true, an empty array or an empty Traversable instance were returned,
		//everything is ok, so continue with the data preparation
        $this->prepareData();
    }
}