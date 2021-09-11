<?php

namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use AntonioPrimera\Bapi\Tests\TestContext\ExceptionTestBapi;
use AntonioPrimera\Bapi\Tests\TestContext\TestException;
use Illuminate\Auth\Access\AuthorizationException;
use Orchestra\Testbench\TestCase;

class BapiExceptionHandlingTest extends TestCase
{
	/** @test */
	public function acceptable_exceptions_will_always_be_thrown()
	{
		$this->expectException(BapiException::class);
		ExceptionTestBapi::run(new BapiException());
	}
	
	/** @test */
	public function by_default_authorization_exceptions_will_always_be_thrown()
	{
		$this->expectException(AuthorizationException::class);
		ExceptionTestBapi::run(new AuthorizationException());
	}
	
	/** @test */
	public function exceptions_which_are_not_in_the_acceptable_exception_list_will_be_handled()
	{
		$this->assertEquals('no exception', ExceptionTestBapi::run(null));
		$this->assertEquals('handled TestException', ExceptionTestBapi::run(new TestException()));
		$this->assertEquals('caught *', ExceptionTestBapi::run(new \Exception()));
	}
}