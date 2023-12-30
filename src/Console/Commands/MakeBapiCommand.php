<?php
namespace AntonioPrimera\Bapi\Console\Commands;

use AntonioPrimera\Artisan\FileGeneratorCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MakeBapiCommand extends FileGeneratorCommand
{
	protected $signature = "make:bapi
		{name : The name of the bapi file, including the relative folder structure}
		{--full : Whether to generate a complex bapi class, with all available methods and hooks (by default it generates a simple bapi class)}
		{--t} : Whether to also generate a test for the bapi (a default named unit test will be generated under tests/Unit/Bapis/BapiNameTest.php)
		{--test=} : Whether to generate a unit test with the given name and the given path"
	;
	
	protected $description = "Create a new Bapi class file in: app/Bapis/";
	
	protected function recipe(): array
	{
		$stubFiles = [
			'simple' => __DIR__ . '/stubs/BapiStubBasic.php.stub',
			'complex' => __DIR__ . '/stubs/BapiStubComplex.php.stub'
		];
		
		$shouldGenerateComplexBapi = $this->option('full')
			|| env('BAPI_GENERATOR_COMPLEX_BAPIS', false);
		
		$stubFile = $shouldGenerateComplexBapi
			? $stubFiles['complex']
			: $stubFiles['simple'];
		
		return [
			'Bapi File' => [
				'stub' => $stubFile,
				'target' => app_path('Bapis'),
				'rootNamespace' => 'App\\Bapis',
				'replace' => [
					'BAPI_BASE_CLASS' => env('BAPI_GENERATOR_BASE_CLASS', 'AntonioPrimera\\Bapi\\Bapi'),
				]
			],
		];
	}
	
	protected function afterFileCreation()
	{
		if ($this->option('test')) {
			Artisan::call('make:test', [
				'name' => $this->option('test') . (str_ends_with($this->option('test'), 'Test') ? '' : 'Test'),
				'-u' => true
			]);
			
			return;
		}
		
		if ($this->option('t') || env('BAPI_GENERATOR_TDD', false)) {
			Artisan::call('make:test', [
				'name' => 'Bapis/' . $this->argument('name') . 'Test',
				'-u' => true
			]);
			
			return;
		}
	}
}