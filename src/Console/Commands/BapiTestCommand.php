<?php

namespace AntonioPrimera\Bapi\Console\Commands;

use Illuminate\Console\Command;

class BapiTestCommand extends Command
{
	protected $signature = 'bapi:test';
	protected $description = 'Bapi test command';
	
	public function handle()
	{
		echo "The BAPI:TEST command is working fine\n";
	}
	
}