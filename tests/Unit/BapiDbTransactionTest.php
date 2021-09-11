<?php

namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Tests\TestContext\CreateUserBapi;
use AntonioPrimera\Bapi\Tests\TestContext\HandleUsersBapi;
use AntonioPrimera\Bapi\Tests\TestContext\Models\TestUser;
use AntonioPrimera\Bapi\Tests\TestContext\UpdateUserBapi;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

class BapiDbTransactionTest extends TestCase
{
	
	//--- TestCase Setup ----------------------------------------------------------------------------------------------
	
	protected function getEnvironmentSetUp($app)
	{
		# Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
		]);
	}
	
	protected function migrate()
	{
		Schema::create('test_users', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('password');
		});
	}
	
	protected function setUp(): void
	{
		parent::setUp();
		
		//create the test_users DB
		$this->migrate();
	}
	
	/** @test */
	public function setup_test___database_is_setup_and_the_user_table_available()
	{
		$this->assertEmpty(TestUser::all());
		
		TestUser::create([
			'name' 		=> 'me',
			'password'	=> '123',
		]);
		
		$this->assertDatabaseCount('test_users', 1);
	}
	
	/** @test */
	public function context_test___the_create_user_bapi_creates_a_user_in_the_db()
	{
		$this->assertEmpty(TestUser::all());
		
		CreateUserBapi::run();
		
		$this->assertDatabaseCount('test_users', 1);
	}
	
	/** @test */
	public function context_test___the_update_user_bapi_updates_the_given_user_in_the_db()
	{
		$user = CreateUserBapi::run('John', '123');
		
		$this->assertDatabaseHas('test_users', ['name' => 'John', 'password' => '123']);
		$this->assertDatabaseMissing('test_users', ['name' => 'Jim', 'password' => '456']);
		
		UpdateUserBapi::run($user, 'Jim', '456');
		$this->assertDatabaseHas('test_users', ['name' => 'Jim', 'password' => '456']);
		$this->assertDatabaseMissing('test_users', ['name' => 'John', 'password' => '123']);
	}
	
	//--- Actual unit tests -------------------------------------------------------------------------------------------
	
	/** @test */
	public function a_successful_bapi_call_will_commit_the_changes_to_the_db()
	{
		$user = CreateUserBapi::run('Mary', '987');
		$this->assertDatabaseHas('test_users', ['name' => 'Mary', 'password' => '987']);
		$this->assertDatabaseCount('test_users', 1);
		
		HandleUsersBapi::run($user, 'Jane', '654', false);
		$this->assertDatabaseHas('test_users', ['name' => 'Jane', 'password' => '654']);
		$this->assertDatabaseCount('test_users', 2);
	}
	
	/** @test */
	public function a_failed_bapi_call_will_rollback_all_changes_to_the_db_made_during_its_lifecycle()
	{
		$user = CreateUserBapi::run('Mary', '987');
		$this->assertDatabaseHas('test_users', ['name' => 'Mary', 'password' => '987']);
		$this->assertDatabaseCount('test_users', 1);
		
		HandleUsersBapi::run($user, 'Jane', '654', true);
		$this->assertDatabaseHas('test_users', ['name' => 'Mary', 'password' => '987']);
		$this->assertDatabaseCount('test_users', 1);
	}
}