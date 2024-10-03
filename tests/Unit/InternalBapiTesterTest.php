<?php

use AntonioPrimera\Bapi\TestInternalBapi;
use AntonioPrimera\Bapi\Tests\TestContext\ExclusiveInternalBapi;
use AntonioPrimera\Bapi\Tests\TestContext\InternalBapi;

it('can call an internal bapi and return its result in a test environment', function () {
	$result = TestInternalBapi::run(ExclusiveInternalBapi::class, result: 40);
	
	expect($result)->toBeArray()
		->toHaveCount(2)
		->toEqual([
			'AntonioPrimera\Bapi\Tests\TestContext\ExclusiveInternalBapi:validate',
			'AntonioPrimera\Bapi\Tests\TestContext\ExclusiveInternalBapi:handle-result:40'
		]);
});

it('throws an exception if the internal bapi tester is used outside of the testing environment', function () {
	//set the environment to production
	app()->detectEnvironment(fn() => 'production');
	
	//expect an exception to be thrown
	expect(fn() => TestInternalBapi::run(ExclusiveInternalBapi::class, result: 40))
		->toThrow(Exception::class);
});

it('throws an exception if the given class is not a subclass of InternalBapi', function () {
	expect(fn() => TestInternalBapi::run(InternalBapi::class, result: 40))
		->toThrow(Exception::class);
});