<?php

namespace AntonioPrimera\Bapi\Tests\Feature;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakeBapiCommandTest extends \Orchestra\Testbench\TestCase
{
	/** @test */
	public function a_bapi_is_successfully_created_when_calling_the_make_bapi_artisan_command()
	{
		// destination path of the Foo class
		$fooClass = app_path('Bapis/TestBapi.php');
		
		// make sure we're starting from a clean state
		if (File::exists($fooClass)) {
			unlink($fooClass);
		}
		
		$this->assertFalse(File::exists($fooClass));
		
		// Run the make command
		Artisan::call('make:bapi Bapis/TestBapi');
		
		// Assert a new file is created
		$this->assertTrue(File::exists($fooClass));
	}
	
	/** @test */
	public function running_the_bapi_test_command()
	{
		// Run the make command
		$result = Artisan::call('bapi:test');
		$this->assertNotNull($result);
	}
}