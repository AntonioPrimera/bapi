<?php

namespace AntonioPrimera\Bapi\Providers;

use AntonioPrimera\Bapi\Console\Commands\MakeBapi;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeBapi::class
			]);
		}
	}
}