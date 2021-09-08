<?php

namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Tests\TestContext\CallTestBapi;
use AntonioPrimera\Bapi\Tests\TestContext\TestModel;
use Prophecy\Call\Call;

class BapiCallTest extends \Orchestra\Testbench\TestCase
{
	/** @test */
	public function a_bapi_can_be_called_statically_by_its_run_method()
	{
		$this->assertTrue(is_array(CallTestBapi::run(new TestModel(), 12)));
	}
	
	/** @test */
	public function a_bapi_can_be_instantiated_and_the_run_method_can_be_called()
	{
		$this->assertTrue(is_array((new CallTestBapi())->run(new TestModel(), 12)));
	}
	
	/** @test */
	public function a_bapi_can_be_invoked()
	{
		$bapi = new CallTestBapi();
		
		$this->assertIsArray($bapi(new TestModel(), 12));
	}
	
	/** @test */
	public function the_handle_method_is_run_when_the_bapi_is_run()
	{
		$bapi = new CallTestBapi();
		
		$this->assertFalse($bapi->handleMethodWasCalled);
		$bapi->run(new TestModel(), 12);
		$this->assertTrue($bapi->handleMethodWasCalled);
	}
	
	/** @test */
	public function the_setup_method_is_called_upon_bapi_construction()
	{
		$bapi = new CallTestBapi();
		
		$this->assertEquals('setup', $bapi->methodCalls[0]);
		$this->assertCount(1, $bapi->methodCalls);
	}
	
	/** @test */
	public function the_constructor_sets_up_the_attribute_list_based_on_the_handle_method()
	{
		$bapi = new CallTestBapi();
		
		$this->assertArraysAreIdentical(
			[
				'testModel' 		=> null,
				'num'				=> null,
				'nullableModel'		=> null,
				'count'				=> 9,
				'msg'				=> 'OK',
				'throwException'	=> false,
			],
			$bapi->exposeAttributes()
		);
	}
	
	/** @test */
	public function the_constructor_arguments_override_default_values_of_the_attributes_defined_in_the_handle_method()
	{
		//constructor does not validate data types, so be careful with this!!!
		$bapi = new CallTestBapi(12, 13, 14, 15, '16', true);
		
		$this->assertArraysAreIdentical(
			[
				'testModel' 		=> 12,
				'num'				=> 13,
				'nullableModel'		=> 14,
				'count'				=> 15,
				'msg'				=> '16',
				'throwException'	=> true,
			],
			$bapi->exposeAttributes()
		);
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