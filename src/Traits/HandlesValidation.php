<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Exceptions\BapiException;

trait HandlesValidation
{
    
    /**
     * Do any manual validations and throw a BapiValidationException or just return false (a BapiValidationException
     * will be thrown if this method returns false). Return true if the validation is successful. Data arriving
     * here should already be pre-validated, so this method is more for business specific validations.
     *
     * @return bool
     */
    protected function validateData() : bool
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
	 * @throws BapiException
	 */
	protected function handleValidationAndDataPreparation() : void
    {
        if (!$this->validateData())
            throw new BapiException();
            
        $this->prepareData();
    }
}