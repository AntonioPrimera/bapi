<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

class AuthorizationTestBapi extends \AntonioPrimera\Bapi\Bapi
{
	public $methodCalls = [];
	
	protected function beforeAuthorization()
	{
		$this->methodCalls[] = 'beforeAuthorization';
	}
	
	protected function authorize()
	{
		$this->methodCalls[] = 'authorize';
		
		return $this->id > 10;
	}
	
	protected function afterAuthorization()
	{
		$this->methodCalls[] = 'afterAuthorization';
	}
	
	public function handle($id)
	{
		return $id;
	}
}