<?php
namespace AntonioPrimera\Bapi\Traits;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

trait HandlesAuthorizationCheck
{
    protected $withAuthorizationCheck = true;
    
    //--- Authorization check control setters -------------------------------------------------------------------------
    
    /**
     * Disables the authorization check for this instance
     * This method can be called via magic method
     *
     * @return $this
     */
    protected function _withoutAuthorizationCheck()
    {
        $this->withAuthorizationCheck = false;
        return $this;
    }
    
    /**
     * Enables the authorization check for this instance
     * This method can be called via magic method
     *
     * @return $this
     */
    protected function _withAuthorizationCheck()
    {
        $this->withAuthorizationCheck = true;
        return $this;
    }
    
    //--- Authorization handling --------------------------------------------------------------------------------------
	
	/**
	 * @throws AuthorizationException
	 */
	protected function handleAuthorization(): static
	{
        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }
        
        return $this;
    }
    
    public function passesAuthorization(): bool
	{
        return (bool) $this->callMethod('authorize');
    }
	
	/**
	 * Override this in case you want to do something else than
	 * throw an AuthorizationException in case of
	 * a failed authorization check
	 *
	 * @throws AuthorizationException
	 */
	protected function failedAuthorization()
    {
        throw new AuthorizationException('This action is unauthorized.');
    }
	
	/**
	 * A wrapper for the Laravel Gate::allows() method
	 *
	 * @param mixed $ability
	 * @param array $arguments
	 *
	 * @return bool
	 */
    protected function can(string $ability, mixed $arguments = []): bool
	{
        return Gate::forUser($this->actor())->allows($ability, $arguments);
    }
}