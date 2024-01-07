<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use AntonioPrimera\Bapi\Tests\TestCase;
use AntonioPrimera\Bapi\Tests\TestContext\ValidatorBapi;
use Illuminate\Validation\ValidationException;

class ValidatesAttributesTest extends TestCase
{
	/** @test */
	public function it_will_throw_a_validation_exception_if_the_bapi_throws_an_exception_with_a_bapi_validation_issue()
	{
		try {
			ValidatorBapi::run(name: 'BapiValidationIssue');
		} catch (ValidationException $validationException) {
			$this->assertEquals('Name error', $validationException->errors()['name'][0]);
		}
	}
	
	/** @test */
	public function it_will_throw_a_validation_exception_if_bapi_throws_a_exception_with_a_list_of_validation_issues()
	{
		try {
			ValidatorBapi::run(name: 'Array');
		} catch (ValidationException $validationException) {
			$this->assertEquals(['Name error'], $validationException->errors()['name']);
			$this->assertEquals(['Age error'], $validationException->errors()['age']);
			$this->assertCount(2, $validationException->errors());
		}
	}
	
	/** @test */
	public function it_will_not_convert_bapi_validation_exceptions_if_they_do_not_contain_bapi_validation_issues()
	{
		try {
			ValidatorBapi::run(name: 'EmptyArray');
		} catch (BapiValidationException $exception) {
			$this->assertEquals([], $exception->validationErrors);
		}
	}
	
	/** @test */
	public function it_will_not_convert_bapi_validation_exceptions_with_a_string_payload()
	{
		try {
			ValidatorBapi::run(name: 'String');
		} catch (BapiValidationException $exception) {
			$this->assertEquals('Some error', $exception->validationErrors);
		}
	}
}