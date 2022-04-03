<?php

namespace AntonioPrimera\Bapi;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * The Actor class is a wrapper for Authenticatable models (mainly Users). It implements itself the Authenticatable
 * interface, but forwards these and all other attribute and method calls to the underlying (wrapped) model.
 * The scope is to always have an actor instance, so we can determine if the actor is authenticated.
 *
 * This allows us to call: $this->actor()->isAuthenticated() inside our bapis.
 */
class Actor implements Authenticatable
{
	protected Authenticatable|null $model;
	
	public function __construct(?Authenticatable $model = null)
	{
		//if a model was provided, set it, otherwise get it from the auth gate
		$this->model = func_num_args() ? $model : auth()->user();
	}
	
	public function isAuthenticated(): bool
	{
		//if the model has an id, we consider it authenticated
		return (bool) $this->getAuthIdentifier();
	}
	
	public function isGuest(): bool
	{
		return !$this->isAuthenticated();
	}
	
	public function getModel(): Authenticatable | null
	{
		return $this->model;
	}
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	/**
	 * Forward all calls to the model
	 */
	public function __get(string $name)
	{
		return $this->model?->$name;
	}
	
	/**
	 * Forward all calls to the model
	 */
	public function __set(string $name, $value): void
	{
		if ($this->model)
			$this->model->$name = $value;
	}
	
	/**
	 * Forward all calls to the model
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->model?->$name(...$arguments);
	}
	
	//--- Authenticatable Interface Implementation --------------------------------------------------------------------
	
	public function getAuthIdentifierName()
	{
		return $this->model?->getAuthIdentifierName();
	}
	
	public function getAuthIdentifier()
	{
		return $this->model?->getAuthIdentifier();
	}
	
	public function getAuthPassword()
	{
		return $this->model?->getAuthPassword();
	}
	
	public function getRememberToken()
	{
		return $this->model?->getRememberToken();
	}
	
	public function setRememberToken($value)
	{
		$this->model?->setRememberToken($value);
	}
	
	public function getRememberTokenName()
	{
		return $this->model?->getRememberTokenName();
	}
}