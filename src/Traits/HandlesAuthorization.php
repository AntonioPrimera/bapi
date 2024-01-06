<?php
namespace AntonioPrimera\Bapi\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

trait HandlesAuthorization
{
	protected Authenticatable|null $actor = null;
    protected bool $withAuthorizationCheck = true;
	
	/**
	 * @throws AuthorizationException
	 */
	protected function handleAuthorization(): static
	{
		$authorized = $this->callMethod('authorize');
		if (!$authorized)
			throw new AuthorizationException('You are not authorized to run ' . class_basename($this));
		
		return $this;
	}
    
	protected function setAuthorizationCheck(bool $authorizationCheck): static
	{
		$this->withAuthorizationCheck = $authorizationCheck;
		return $this;
	}
	
	//--- Helpers -----------------------------------------------------------------------------------------------------
	
	/**
	 * Change the actor running this bapi. By default, the bapi is run by the
	 * currently authenticated user, but this can be changed by calling
	 * this method and providing another Authenticatable instance
	 */
	public function actingAs(Authenticatable|null $actor) : static
	{
		$this->actor = $actor;
		return $this;
	}
	
	/**
	 * Return the actor for the current bapi. If not specified
	 * using the actingAs method, the actor/user which is
	 * currently authenticated will be returned
	 */
	public function actor() : Authenticatable | null
	{
		//todo: in order to make this Laravel independent, this should be extracted into a Laravel plugin
		//todo: the Laravel plugin should load this during boot (in a service provider)
		if (!$this->actor)
			$this->actor = auth()->user();
	
		return $this->actor;
	}
	
	/**
	 * A wrapper for the Laravel Gate::allows() method
	 */
    protected function can(string $ability, mixed $arguments = []): bool
	{
		//todo: in order to make this Laravel independent, this should be extracted into a Laravel plugin
		//todo: the Laravel plugin should load this during boot (in a service provider)
        return Gate::forUser($this->actor())->allows($ability, $arguments);
    }
}