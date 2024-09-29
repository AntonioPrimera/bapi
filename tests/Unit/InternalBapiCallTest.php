<?php

use AntonioPrimera\Bapi\Exceptions\BapiException;
use AntonioPrimera\Bapi\Tests\TestContext\CallerOfExclusiveInternalBapi;
use AntonioPrimera\Bapi\Tests\TestContext\CallerOfInternalBapi;
use AntonioPrimera\Bapi\Tests\TestContext\ExclusiveInternalBapi;
use AntonioPrimera\Bapi\Tests\TestContext\InternalBapi;

it('allows a bapi to call another bapi internally, skipping authorization by default', function () {
	$result = CallerOfInternalBapi::run(value: 22, multiplier: 2);
	expect($result)->toBeArray()->toHaveCount(5)
		->and($result)->toBe([
			CallerOfInternalBapi::class . ':authorize',
			CallerOfInternalBapi::class . ':validate',
			CallerOfInternalBapi::class . ':handle',
			InternalBapi::class . ':validate',
			InternalBapi::class . ':handle-result:44'
		]);
});

it('allows a bapi to call another bapi internally, with authorization check', function () {
	$result = CallerOfInternalBapi::run(value: 33, multiplier: 2, callWithAuthorization: true);
	expect($result)->toBeArray()->toHaveCount(6)
		->and($result)->toBe([
			CallerOfInternalBapi::class . ':authorize',
			CallerOfInternalBapi::class . ':validate',
			CallerOfInternalBapi::class . ':handle',
			InternalBapi::class . ':authorize',
			InternalBapi::class . ':validate',
			InternalBapi::class . ':handle-result:66'
		]);
});

it('can call an exclusively internal bapi', function () {
	$result = CallerOfExclusiveInternalBapi::run(value: 11, multiplier: 3);
	expect($result)->toBeArray()->toHaveCount(5)
		->and($result)->toBe([
			CallerOfExclusiveInternalBapi::class . ':authorize',
			CallerOfExclusiveInternalBapi::class . ':validate',
			CallerOfExclusiveInternalBapi::class . ':handle',
			ExclusiveInternalBapi::class . ':validate',
			ExclusiveInternalBapi::class . ':handle-result:33'
		]);
});

it('can not call an exclusively internal bapi using the run method', function () {
	expect(fn() => ExclusiveInternalBapi::run(result: 22))->toThrow(Error::class);
});

it('can not use an internal call from outside of a bapi', function () {
	expect(InternalBapi::run(result: 22))->toBeArray()->toHaveCount(3)
		->and(fn() => InternalBapi::call(result: 22))->toThrow(BapiException::class);
});