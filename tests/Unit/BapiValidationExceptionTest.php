<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class BapiValidationExceptionTest extends \AntonioPrimera\Bapi\Tests\TestCase
{
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_single_bapi_validation_error_as_an_argument()
	{
		$validationError = new BapiValidationIssue('name', 'George', 'female-name-required');
		$exception = new BapiValidationException($validationError);
		
		$this->assertSame($validationError, $exception->validationErrors);
	}
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_list_of_validation_errors_as_argument()
	{
		$nameValidationError = new BapiValidationIssue('name', 'George', 'female-name-required');
		$ageValidationError = new BapiValidationIssue('age', 16, 'too-young');
		
		$exception = new BapiValidationException([$nameValidationError, $ageValidationError]);
		
		$this->assertSame([$nameValidationError, $ageValidationError], $exception->validationErrors);
	}
}