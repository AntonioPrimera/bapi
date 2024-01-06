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
	
	/** @test */
	public function it_can_also_create_a_specific_test_file_if_the_proper_command_option_was_given()
	{
		$bapiName = 'TestSuite/TestBapi';
		$bapiFileName = $this->bapiFileName($bapiName);
		$this->cleanup($bapiFileName);
		
		//cleanup the target test file
		$testName = base_path('tests/Unit/Bp/MyBapiTest.php');
		$this->cleanupTestFile($testName);
		
		//run the make command
		Artisan::call("make:bapi {$bapiName} --test Bp/MyBapi");
		
		//assert a new file is created
		$this->assertTrue(File::exists($bapiFileName));
		//assert the test was created
		$this->assertTrue(File::exists($testName));
		
		
		//delete the generated file
		$this->cleanup($bapiFileName);
		$this->cleanupTestFile($testName);
	}
	
	/** @test */
	public function it_can_also_create_a_default_test_file_if_the_proper_command_option_was_given()
	{
		$bapiName = 'TestSuite/TestBapi';
		$bapiFileName = $this->bapiFileName($bapiName);
		$this->cleanup($bapiFileName);
		
		//cleanup the target test file
		$testName = base_path('tests/Unit/Bapis/TestSuite/TestBapiTest.php');
		$this->cleanupTestFile($testName);
		
		//run the make command
		Artisan::call("make:bapi {$bapiName} --t");
		
		//assert a new file is created
		$this->assertTrue(File::exists($bapiFileName));
		//assert the test was created
		$this->assertTrue(File::exists($testName));
		
		//delete the generated file
		$this->cleanup($bapiFileName);
		$this->cleanupTestFile($testName);
	}
	
	/** @test */
	public function it_will_create_a_default_test_file_if_the_proper_environment_key_is_set()
	{
		$bapiName = 'TestSuite/TestBapi';
		$bapiFileName = $this->bapiFileName($bapiName);
		$this->cleanup($bapiFileName);
		
		//cleanup the target test file
		$testName = base_path('tests/Unit/Bapis/TestSuite/TestBapiTest.php');
		$this->cleanupTestFile($testName);
		
		//set the proper env entry
		putenv("BAPI_GENERATOR_TDD=true");
		
		//run the make command
		Artisan::call("make:bapi {$bapiName}");
		
		//remove the env entry
		putenv("BAPI_GENERATOR_TDD=false");
		
		//assert a new file is created
		$this->assertTrue(File::exists($bapiFileName));
		//assert the test was created
		$this->assertTrue(File::exists($testName));
		
		//delete the generated file
		$this->cleanup($bapiFileName);
		$this->cleanupTestFile($testName);
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
	
	protected function cleanupTestFile(string $fileName)
	{
		if (File::exists($fileName)) {
			unlink($fileName);
		}
		
		$this->assertFalse(File::exists($fileName));
	}
}