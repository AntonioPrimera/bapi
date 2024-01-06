<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use AntonioPrimera\Bapi\Tests\TestContext\CreateCompanyBapi;
use Illuminate\Support\Collection;

class BapiValidationTest extends \AntonioPrimera\Bapi\Tests\TestCase
{
	
	//--- Validation Passing Tests ------------------------------------------------------------------------------------
	
	/** @test */
	public function returning_true_from_the_validate_method_should_pass_the_validation()
	{
		$this->assertEquals('True', CreateCompanyBapi::run(name: 'True'));
	}
	
	//--- Validation Failure Tests ------------------------------------------------------------------------------------
	
	/** @test */
	public function returning_an_empty_array_from_the_validate_method_should_not_pass_the_validation()
	{
		$this->expectException(BapiValidationException::class);
		CreateCompanyBapi::run(name: 'EmptyArray');
	}
	
	/** @test */
	public function returning_false_from_the_validate_method_should_throw_an_empty_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run(name: 'False');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertTrue($bapiValidationException->validationErrors === false);
		}
	}
	
	/** @test */
	public function returning_an_issue_instance_from_the_validate_method_should_throw_a_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run(name: 'Issue');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertInstanceOf(BapiValidationIssue::class, $bapiValidationException->validationErrors);
			$this->assertEquals('name', $bapiValidationException->validationErrors->attribute);
			$this->assertEquals('Issue', $bapiValidationException->validationErrors->value);
			$this->assertEquals('Some error', $bapiValidationException->validationErrors->error);
		}
	}
	
	/** @test */
	public function returning_an_array_of_issues_from_the_validate_method_should_throw_a_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run(name: 'Array');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertIsArray($bapiValidationException->validationErrors);
			$this->assertCount(1, $bapiValidationException->validationErrors);
			$this->assertInstanceOf(
				BapiValidationIssue::class,
				$bapiValidationException->validationErrors[0]
			);
		}
	}
	
	/** @test */
	public function throwing_a_bapi_validation_exception_from_the_validate_method_should_throw_that_exception()
	{
		try {
			CreateCompanyBapi::run(name: 'Exception');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertEquals('Specific errors', $bapiValidationException->validationErrors);
		}
	}
	
}