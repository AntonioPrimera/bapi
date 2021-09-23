<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;

/**
 * @method run(TestModel $testModel, int $num, ?TestModel $nullableModel = null, int $count = 9, $msg = 'OK', \Exception|bool $throwException = false)
 */
class CallTestBapi extends Bapi
{
	public $handleMethodWasCalled = false;
	public $methodCalls = [];
	public $attributeLists = [];
	
	public function handle(
		TestModel $testModel,
		int $num,
		?TestModel $nullableModel = null,
		int $count = 9,
		$msg = 'OK',
		\Exception|bool $throwException = false
	)
	{
		$this->methodCalls[] = 'handle';
		$this->handleMethodWasCalled = true;
		$this->attributeLists['handle'] = $this->attributes;
		
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
	
	//--- Hooks -------------------------------------------------------------------------------------------------------
	
	protected function setup()
	{
		$this->methodCalls[] = 'setup';
	}
	
	protected function prepareData(): void
	{
		$this->attributeLists['prepareData'] = $this->attributes;
		$this->methodCalls[] = 'prepareData';
	}
	
	protected function validate(): bool
	{
		$this->attributeLists['validate'] = $this->attributes;
		$this->methodCalls[] = 'validate';
		
		return strlen($this->msg) < 10;
	}
	
	protected function beforeAuthorization()
	{
		$this->attributeLists['beforeAuthorization'] = $this->attributes;
		$this->methodCalls[] = 'beforeAuthorization';
	}
	
	protected function authorize()
	{
		$this->attributeLists['authorize'] = $this->attributes;
		$this->methodCalls[] = 'authorize';
		
		return true;
	}
	
	protected function afterAuthorization()
	{
		$this->attributeLists['afterAuthorization'] = $this->attributes;
		$this->methodCalls[] = 'afterAuthorization';
	}
	
	protected function beforeHandle()
	{
		$this->attributeLists['beforeHandle'] = $this->attributes;
		$this->methodCalls[] = 'beforeHandle';
	}
	
	protected function afterHandle($result): mixed
	{
		$this->attributeLists['afterHandle'] = $this->attributes;
		$this->methodCalls[] = 'afterHandle';
		
		if ($this->msg === 'AHC')
			$result['afterHandleChanges'] = true;
		
		return $result;
	}
}