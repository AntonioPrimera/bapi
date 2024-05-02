<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BapiValidationIssueTest extends TestCase
{
	#[Test]
	public function a_bapi_validation_error_can_be_created_and_its_data_is_accessible()
	{
		$bapiValidationError = new BapiValidationIssue('name', 'George', 'not-unique', 123);
		
		$this->assertInstanceOf(BapiValidationIssue::class, $bapiValidationError);
		$this->assertEquals('name', $bapiValidationError->attributeName);
		$this->assertEquals('George', $bapiValidationError->attributeValue);
		$this->assertEquals('not-unique', $bapiValidationError->errorMessage);
		$this->assertEquals(123, $bapiValidationError->errorCode);
	}
	
	#[Test]
	public function the_attributes_of_a_bapi_validation_error_can_not_be_changed_directly()
	{
		/** @noinspection PhpObjectFieldsAreOnlyWrittenInspection */
		$bapiValidationError = new BapiValidationIssue('name', 'George', 'not-unique', 123);
		
		$this->expectException(\Error::class);
		/** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */
		$bapiValidationError->attributeName = 'age';
	}
}