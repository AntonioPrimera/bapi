<?php

namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use Illuminate\Support\Collection;

class BapiValidationExceptionTest extends \AntonioPrimera\Bapi\Tests\TestCase
{
	
	/** @test */
	public function a_bapi_validation_exception_can_receive_a_single_bapi_validation_error_as_an_argument()
	{
		$validationError = new BapiValidationIssue('name', 'George', 'female-name-required');
		$exception = new BapiValidationException($validationError);
		
		$this->assertInstanceOf(Collection::class, $exception->validationErrors);
		$this->assertCount(1, $exception->validationErrors);
		$this->assertTrue($exception->validationErrors->first() === $validationError);
	}
	
	/** @test */
	public function a_bapi_validation_exception_can_receive_a_list_of_validation_errors_as_argument()
	{
		$nameValidationError = new BapiValidationIssue('name', 'George', 'female-name-required');
		$ageValidationError = new BapiValidationIssue('age', 16, 'too-young');
		
		$exception = new BapiValidationException([$nameValidationError, $ageValidationError]);
		
		$this->assertInstanceOf(Collection::class, $exception->validationErrors);
		$this->assertCount(2, $exception->validationErrors);
		$this->assertTrue($exception->validationErrors->first() === $nameValidationError);
		$this->assertTrue($exception->validationErrors->last() === $ageValidationError);
	}
	
	/** @test */
	public function a_list_of_validation_errors_can_be_set_also_after_creating_the_exception()
	{
		$nameValidationError = new BapiValidationIssue('name', 'George', 'female-name-required');
		$ageValidationError = new BapiValidationIssue('age', 16, 'too-young');
		
		$exception = new BapiValidationException();
		
		$exception->setValidationErrors($nameValidationError);
		$this->assertCount(1, $exception->validationErrors);
		
		$exception->setValidationErrors(collect([$nameValidationError, $ageValidationError]));
		$this->assertCount(2, $exception->validationErrors);
	}
}