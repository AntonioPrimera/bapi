<?php

namespace AntonioPrimera\Bapi\Providers;

use AntonioPrimera\Bapi\Console\Commands\BapiTestCommand;
use AntonioPrimera\Bapi\Console\Commands\MakeBapi;
use Illuminate\Support\ServiceProvider;

class BapiPackageServiceProvider extends ServiceProvider
{
	
	public function boot()
	{
		//dd("Service Provider Boot Method running...");
		//if ($this->app->runningInConsole()) {
			$this->commands([
				MakeBapi::class,
				BapiTestCommand::class,
			]);
		//}
	}
}