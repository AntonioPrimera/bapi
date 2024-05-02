<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Tests\TestContext\CallTestBapi;
use AntonioPrimera\Bapi\Tests\TestContext\TestModel;
use PHPUnit\Framework\Attributes\Test;

class BapiCallTest extends \Orchestra\Testbench\TestCase
{
	#[Test]
	public function a_bapi_can_be_called_statically_by_its_run_method()
	{
		$this->assertTrue(is_array(CallTestBapi::run(testModel: new TestModel(), num: 12)));
	}
	
	#[Test]
	public function a_bapi_can_be_instantiated_and_the_run_method_can_be_called()
	{
		$this->assertTrue(is_array((new CallTestBapi())->run(testModel: new TestModel(), num: 12)));
	}
	
	#[Test]
	public function a_bapi_can_be_invoked()
	{
		$bapi = new CallTestBapi();
		
		$this->assertIsArray($bapi(testModel: new TestModel(), num: 12));
	}
	
	#[Test]
	public function the_handle_method_is_run_when_the_bapi_is_run()
	{
		$bapi = new CallTestBapi();
		
		$this->assertFalse($bapi->handleMethodWasCalled);
		$bapi->run(testModel: new TestModel(),  num: 12);
		$this->assertTrue($bapi->handleMethodWasCalled);
	}
	
	#[Test]
	public function on_running_the_bapi_the_arguments_provided_are_available_to_all_methods_in_the_run_lifecycle()
	{
		$bapi = new CallTestBapi();
		
		$this->assertEmpty($bapi->attributeLists);
		
		$testModel = new TestModel();
		$nullableModel = new TestModel();
		
		$bapi->run(testModel: $testModel, num: 12, nullableModel: $nullableModel, count: 123, msg: 'OKEY', throwException: false);
		
		$expectedAttributes = [
			'testModel' 		=> $testModel,
			'num'				=> 12,
			'nullableModel'		=> $nullableModel,
			'count'				=> 123,
			'msg'				=> 'OKEY',
			'throwException'	=> false,
		];
		
		$this->assertArraysAreIdentical($expectedAttributes, $bapi->attributeLists['validate']);
		$this->assertArraysAreIdentical($expectedAttributes, $bapi->attributeLists['handle']);
		$this->assertArraysAreIdentical($expectedAttributes, $bapi->attributeLists['processResult']);
	}
	
	#[Test]
	public function the_bapi_has_a_strict_lifecycle_calling_all_hooks()
	{
		$bapi = new CallTestBapi();
		
		$this->assertCount(0, $bapi->methodCalls);
		
		$bapi->run(new TestModel(), 12, null, 123, 'OKEY', false);
		
		$this->assertCount(4, $bapi->methodCalls);
		
		$this->assertEquals('authorize', 			$bapi->methodCalls[0]);
		$this->assertEquals('validate',	 		$bapi->methodCalls[1]);
		$this->assertEquals('handle', 				$bapi->methodCalls[2]);
		$this->assertEquals('processResult', 		$bapi->methodCalls[3]);
	}
	
	#[Test]
	public function the_after_handle_hook_can_change_the_result_of_the_handle_method_before_returning_the_bapi_result()
	{
		$testModel = new TestModel();
		
		$bapi = new CallTestBapi();
		$result = $bapi->run(testModel: $testModel, num: 12, count: 123, msg: 'OKEY');
		
		$this->assertArrayNotHasKey('processResultChanges', $result);
		
		$bapi = new CallTestBapi();
		$result = $bapi->run(testModel: new $testModel, num: 12, nullableModel: null, count: 123, msg: 'AHC');
		
		$this->assertArrayHasKey('processResultChanges', $result);
	}
	
	//--- Helpers -----------------------------------------------------------------------------------------------------
	
	protected function assertArraysAreIdentical($expected, $array)
	{
		$this->assertSameSize($expected, $array);
		
		foreach ($expected as $name => $defaultValue) {
			$this->assertArrayHasKey($name, $array);
			$this->assertTrue($defaultValue === $array[$name]);
		}
	}
}