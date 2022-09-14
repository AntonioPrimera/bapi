<?php
namespace AntonioPrimera\Bapi\Console\Commands;

use AntonioPrimera\Artisan\FileGeneratorCommand;

class MakeBapiCommand extends FileGeneratorCommand
{
	protected $signature = "make:bapi {name}";
	protected $description = "Create a new Bapi class file in: app/Bapis/";
	
	protected function recipe(): array
	{
		return [
			'Bapi File' => [
				'stub' => __DIR__ . '/stubs/BapiStub.php.stub',
				'path' => 'Bapis',
				'rootNamespace' => 'App\\Bapis',
				'replace' => [
					'BAPI_BASE_CLASS' => env('BAPI_GENERATOR_BASE_CLASS', 'AntonioPrimera\\Bapi\\Bapi'),
				]
			],
		];
	}
}