<?php

namespace AntonioPrimera\Bapi\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeBapi extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:bapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Bapi';
    
    protected $type = 'Bapi';

    protected function getStub()
	{
		return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'BapiStub.stub';
	}
	
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace . '\\Bapis';
	}
}
