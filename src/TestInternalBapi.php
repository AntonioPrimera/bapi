<?php
namespace AntonioPrimera\Bapi;

class TestInternalBapi extends Bapi
{
	public static function run(string $bapi, ...$args)
	{
		//if we are not in the test environment, throw an exception
		if (!app()->environment('testing'))
			throw new \Exception('The internal bapi tester is only intended to be used in the testing environment');
		
		//if the given class is not a subclass of InternalBapi, throw an exception
		if (!is_subclass_of($bapi, InternalBapi::class))
			throw new \Exception('Given class is not an InternalBapi');
		
		return call_user_func([$bapi, 'call'], ...$args);
	}
}