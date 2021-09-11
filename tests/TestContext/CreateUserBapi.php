<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Tests\TestContext\Models\TestUser;
use Illuminate\Support\Str;

class CreateUserBapi extends \AntonioPrimera\Bapi\Bapi
{
	
	protected function handle($name = null, $password = null)
	{
		return TestUser::create([
			'name' 		=> $name ?: Str::random(),
			'password'	=> $password ?: Str::random(),
		]);
	}
}