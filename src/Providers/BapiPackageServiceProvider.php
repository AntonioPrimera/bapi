<?php
namespace AntonioPrimera\Bapi\Providers;

use AntonioPrimera\Bapi\Console\Commands\MakeBapiCommand;
use Illuminate\Support\ServiceProvider;

class BapiPackageServiceProvider extends ServiceProvider
{
	
	public function boot(): void
	{
		$this->publishes([
			__DIR__ . '/../../config/bapi.php' => config_path('bapi.php')
		], 'bapi-config');
		
		if ($this->app->runningInConsole()) {
			$this->commands([
				MakeBapiCommand::class
			]);
		}
	}
	
	//public function register(): void
	//{
	//	$this->mergeConfigFrom(__DIR__ . '/../../config/bapi.php', 'bapi');
	//}
}