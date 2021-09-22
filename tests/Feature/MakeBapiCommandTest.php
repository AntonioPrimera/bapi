<?php

namespace AntonioPrimera\Bapi\Tests\Feature;


use AntonioPrimera\Bapi\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakeBapiCommandTest extends TestCase
{
	/** @test */
	public function a_bapi_is_successfully_created_when_calling_the_make_bapi_artisan_command()
	{
		$bapiName = 'TestSuite/TestBapi';
		$bapiFileName = $this->bapiFileName($bapiName);
		$this->cleanup($bapiFileName);
		
		//run the make command
		Artisan::call("make:bapi {$bapiName}");
		
		//assert a new file is created
		$this->assertTrue(File::exists($bapiFileName));
		
		//delete the generated file
		$this->cleanup($bapiFileName);
	}
	
	/** @test */
	public function a_generated_bapi_can_be_run_without_doing_anything()
	{
		$bapiName = 'TestSuite/TestBapi';
		$bapiFileName = $this->bapiFileName($bapiName);
		
		$this->cleanup($bapiFileName);
		
		//run the make command and make sure the bapi is generated
		Artisan::call("make:bapi {$bapiName}");
		$this->assertTrue(File::exists($bapiFileName));
		
		//we need to require this file, because it was just generated
		require_once $bapiFileName;
		
		//create a new bapi instance
		$bapi = $this->createBapiInstance($bapiName);
		
		//make sure it runs and no syntax or other errors occur
		$this->assertNull($bapi->withoutAuthorizationCheck()->run());
		
		$this->cleanup($bapiFileName);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function bapiFileName($bapiName)
	{
		return app_path("Bapis/{$bapiName}.php");
	}
	
	protected function cleanup($bapiFileName)
	{
		//make sure we're starting with a clean state
		if (File::exists($bapiFileName)) {
			unlink($bapiFileName);
		}
		
		$this->assertFalse(File::exists($bapiFileName));
	}
	
	protected function createBapiInstance($bapiName)
	{
		$bapiClassName = "\\App\\Bapis\\" . str_replace('/', '\\', $bapiName);
		return new $bapiClassName();
	}
}