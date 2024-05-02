<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Tests\TestContext\AuthorizationTestBapi;
use Illuminate\Auth\Access\AuthorizationException;
use PHPUnit\Framework\Attributes\Test;

class BapiAuthorizationTest extends \Orchestra\Testbench\TestCase
{
	#[Test]
	public function a_bapi_will_run_if_the_authorization_is_successful()
	{
		$this->assertEquals(100, AuthorizationTestBapi::run(id: 100));
	}
	
	#[Test]
	public function a_bapi_will_throw_an_authorization_exception_on_authorization_failure()
	{
		$this->expectException(AuthorizationException::class);
		AuthorizationTestBapi::run(id: 5);
	}
	
	#[Test]
	public function a_bapi_can_be_called_without_authorization_check()
	{
		//the bapi authorization should fail for a parameter less or equal with 10
		$this->assertEquals(5, AuthorizationTestBapi::withoutAuthorizationCheck()->run(id: 5));
	}
	
	#[Test]
	public function a_bapi_will_run_the_before_and_after_authorization_hooks()
	{
		$bapi = new AuthorizationTestBapi();
		
		$this->assertEmpty($bapi->methodCalls);
		
		$bapi->run(id: 100);
		
		$this->assertEquals(['authorize'], $bapi->methodCalls);
	}
}