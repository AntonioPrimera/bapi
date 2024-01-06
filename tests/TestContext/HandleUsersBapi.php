<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Tests\TestContext\Models\TestUser;
use Exception;

/**
 * @method static array run(TestUser $testUserToUpdate, string $name = null, string $password = null, bool $throwException = false)
 */
class HandleUsersBapi extends \AntonioPrimera\Bapi\Bapi
{
	
	protected function handle(TestUser $testUserToUpdate, $name = null, $password = null, $throwException = false)
	{
		$user = CreateUserBapi::run('John', '123');
		UpdateUserBapi::run(testUser: $testUserToUpdate, name: $name, password: $password);
		
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