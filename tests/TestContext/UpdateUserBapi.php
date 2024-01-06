<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Tests\TestContext\Models\TestUser;

/**
 * @method static TestUser run(TestUser $testUser, string $name = null, string $password = null)
 */
class UpdateUserBapi extends \AntonioPrimera\Bapi\Bapi
{
	
	protected function handle(TestUser $testUser, $name = null, $password = null)
	{
		if ($name)
			$testUser->name = $name;
		
		if ($password)
			$testUser->password = $password;
		
		$testUser->save();
		
		return $testUser;
	}
}