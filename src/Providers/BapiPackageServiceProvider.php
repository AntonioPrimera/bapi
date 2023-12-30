<?php

namespace AntonioPrimera\Bapi\Providers;

use AntonioPrimera\Bapi\Console\Commands\MakeBapiCommand;
use Illuminate\Support\ServiceProvider;

class BapiPackageServiceProvider extends ServiceProvider
{
	
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeBapiCommand::class
			]);
		}
	}
}