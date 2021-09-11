<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Tests\TestContext\Models\TestUser;
use Exception;

class HandleUsersBapi extends \AntonioPrimera\Bapi\Bapi
{
	
	protected function handle(TestUser $testUserToUpdate, $name = null, $password = null, $throwException = false)
	{
		$user = CreateUserBapi::run('John', '123');
		UpdateUserBapi::run($testUserToUpdate, $name, $password);
		
		if ($throwException)
			throw new \Exception('thrown');
		
		return compact('user', 'testUserToUpdate');
	}
	
	protected function handleException(Exception $exception)
	{
		//don't throw any exception
		return false;
	}
}