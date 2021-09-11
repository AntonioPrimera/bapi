<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use Exception;

class ExceptionTestBapi extends \AntonioPrimera\Bapi\Bapi
{
	
	protected function handle(?\Exception $exception)
	{
		if ($exception)
			throw $exception;
		
		return 'no exception';
	}
	
	protected function handleException(Exception $exception)
	{
		if ($exception instanceof TestException)
			return 'handled TestException';
		
		return 'caught *';
	}
}