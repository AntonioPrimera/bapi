<?php

namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;

class BapiValidationIssueTest extends \AntonioPrimera\Bapi\Tests\TestCase
{
	/** @test */
	public function a_bapi_validation_error_can_be_created_and_its_data_is_accessible()
	{
		$bapiValidationError = new BapiValidationIssue('name', 'George', 'not-unique', 123);
		
		$this->assertInstanceOf(BapiValidationIssue::class, $bapiValidationError);
		$this->assertEquals('name', $bapiValidationError->attribute);
		$this->assertEquals('George', $bapiValidationError->value);
		$this->assertEquals('not-unique', $bapiValidationError->error);
		$this->assertEquals(123, $bapiValidationError->errorCode);
	}
	
	/** @test */
	public function the_attributes_of_a_bapi_validation_error_can_not_be_changed_directly()
	{
		$bapiValidationError = new BapiValidationIssue('name', 'George', 'not-unique', 123);
		
		$this->expectException(\Error::class);
		$bapiValidationError->attribute = 'age';
	}
	
	/** @test */
	public function the_attributes_of_a_bapi_validation_error_can_be_updated_via_setters()
	{
		$bapiValidationError = new BapiValidationIssue('name', 'George', 'not-unique', 123);
		
		$bapiValidationError->setAttribute('age');
		$bapiValidationError->setValue(16);
		$bapiValidationError->setError('You are not of legal age!');
		$bapiValidationError->setErrorCode('N-AGE-18');
		
		$this->assertEquals('age', $bapiValidationError->attribute);
		$this->assertEquals(16, $bapiValidationError->value);
		$this->assertEquals('You are not of legal age!', $bapiValidationError->error);
		$this->assertEquals('N-AGE-18', $bapiValidationError->errorCode);
	}
}