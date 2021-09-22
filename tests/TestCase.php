<?php

namespace AntonioPrimera\Bapi\Tests;

use AntonioPrimera\Bapi\Providers\BapiPackageServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends \Orchestra\Testbench\TestCase
{
	use RefreshDatabase;
	
	//protected function setUp(): void
	//{
	//	parent::setUp();
	//
	//	//migrate the test users table
	//	TestUsersTable::migrate();
	//}
	
	protected function getPackageProviders($app)
	{
		return [
			BapiPackageServiceProvider::class,
		];
	}
}