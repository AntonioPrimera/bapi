<?php

namespace AntonioPrimera\Bapi\Tests\Feature;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakeBapiCommandTest extends \Orchestra\Testbench\TestCase
{
	/** @test */
	public function a_bapi_is_successfully_created_when_calling_the_make_bapi_artisan_command()
	{
		//I DIDN'T MANAGE TO GET THIS TEST UP AND RUNNING. I ALWAYS GET COMMAND NOT FOUND
		//IF SOMEBODY SEES THIS AND HAS AN IDEA ON HOW TO SOLVE THIS, PLEASE CREATE A PR.
		//THE COMMAND RUNS, EVEN IF THE TEST FAILS - WOULD BE NICE TO ALSO HAVE IT TESTED
		
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
}