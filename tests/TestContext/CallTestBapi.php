<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;

/**
 * @method run(TestModel $testModel, int $num, TestModel $nullableModel = null, int $count = 9, $msg = 'OK', \Exception|bool $throwException = false)
 */
class CallTestBapi extends Bapi
{
	public $handleMethodWasCalled = false;
	public $methodCalls = [];
	
	protected function setup()
	{
		$this->methodCalls[] = 'setup';
	}
	
	public function handle(
		TestModel $testModel,
		int $num,
		TestModel $nullableModel = null,
		int $count = 9,
		$msg = 'OK',
		\Exception|bool $throwException = false
	)
	{
		$this->handleMethodWasCalled = true;
		
		//if an exception instance was given, throw it
		//if boolean true was given throw a new generic exception
		if ($throwException)
			throw ($throwException instanceof \Exception ? $throwException : new \Exception());
		
		return compact('testModel', 'num', 'nullableModel', 'count', 'msg', 'throwException');
	}
	
	public function exposeAttributes()
	{
		return $this->attributes;
	}
}