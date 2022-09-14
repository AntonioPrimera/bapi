<?php
namespace AntonioPrimera\Bapi\Console\Commands;

use AntonioPrimera\Artisan\FileGeneratorCommand;

class MakeBapiCommand extends FileGeneratorCommand
{
	protected $signature = "make:bapi
		{name : The name of the bapi file, including the relative folder structure}
		{--full : Whether to generate a complex bapi class, with all available methods and hooks (by default it generates a simple bapi class)}";
	
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
				'path' => 'Bapis',
				'rootNamespace' => 'App\\Bapis',
				'replace' => [
					'BAPI_BASE_CLASS' => env('BAPI_GENERATOR_BASE_CLASS', 'AntonioPrimera\\Bapi\\Bapi'),
				]
			],
		];
	}
}