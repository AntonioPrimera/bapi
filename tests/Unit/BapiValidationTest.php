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
		$this->assertEquals('True', CreateCompanyBapi::run('True'));
	}
	
	/** @test */
	public function returning_an_empty_array_from_the_validate_method_should_pass_the_validation()
	{
		$this->assertEquals('EmptyArray', CreateCompanyBapi::run('EmptyArray'));
	}
	
	/** @test */
	public function returning_an_empty_collection_from_the_validate_method_should_pass_the_validation()
	{
		$this->assertEquals('EmptyCollection', CreateCompanyBapi::run('EmptyCollection'));
	}
	
	//--- Validation Failure Tests ------------------------------------------------------------------------------------
	
	/** @test */
	public function returning_false_from_the_validate_method_should_throw_an_empty_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run('False');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertInstanceOf(Collection::class, $bapiValidationException->validationErrors);
			$this->assertEmpty($bapiValidationException->validationErrors);
		}
	}
	
	/** @test */
	public function returning_an_issue_instance_from_the_validate_method_should_throw_a_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run('Issue');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertInstanceOf(Collection::class, $bapiValidationException->validationErrors);
			$this->assertCount(1, $bapiValidationException->validationErrors);
			$this->assertInstanceOf(
				BapiValidationIssue::class,
				$bapiValidationException->validationErrors->first()
			);
		}
	}
	
	/** @test */
	public function returning_an_array_of_issues_from_the_validate_method_should_throw_a_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run('Array');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertInstanceOf(Collection::class, $bapiValidationException->validationErrors);
			$this->assertNotEmpty($bapiValidationException->validationErrors);
			$this->assertInstanceOf(
				BapiValidationIssue::class,
				$bapiValidationException->validationErrors->first()
			);
		}
	}
	
	/** @test */
	public function returning_a_collection_of_issues_from_the_validate_method_should_throw_a_bapi_validation_exception()
	{
		try {
			CreateCompanyBapi::run('Collection');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertInstanceOf(Collection::class, $bapiValidationException->validationErrors);
			$this->assertNotEmpty($bapiValidationException->validationErrors);
			$this->assertInstanceOf(
				BapiValidationIssue::class,
				$bapiValidationException->validationErrors->first()
			);
		}
	}
	
	/** @test */
	public function throwing_a_bapi_validation_exception_from_the_validate_method_should_throw_that_exception()
	{
		try {
			CreateCompanyBapi::run('Exception');
		} catch (BapiValidationException $bapiValidationException) {
			$this->assertEmpty($bapiValidationException->validationErrors);
		}
	}
	
}