<?php

namespace AntonioPrimera\Bapi\Tests\TestContext\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class TestUser extends \Illuminate\Database\Eloquent\Model implements Authenticatable
{
	protected $connection = 'testbench';
	protected $table = 'test_users';
	protected $guarded = [];
	public $timestamps = false;
	
	protected $wonderful;
	public $special = 'yes';
	
	public function getAuthIdentifierName()
	{
		return 'John';
	}
	
	public function getAuthIdentifier()
	{
		return 64;
	}
	
	public function getAuthPassword()
	{
		return 'pass';
	}
	
	public function getRememberToken()
	{
		return 'remember-token';
	}
	
	public function setRememberToken($value)
	{
		//do nothing
	}
	
	public function getRememberTokenName()
	{
		return 'token-name';
	}
	
	//--- Additional methods ------------------------------------------------------------------------------------------
	
	public function getWonderfulAttribute()
	{
		return Str::kebab($this->wonderful);
	}
	
	public function setWonderfulAttribute($value)
	{
		$this->wonderful = $value;
	}
	
	public function addition($a, $b)
	{
		return $a + $b;
	}
}