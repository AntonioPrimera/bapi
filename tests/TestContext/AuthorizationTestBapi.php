<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;

class AuthorizationTestBapi extends Bapi
{
	public $methodCalls = [];
	
	protected function authorize()
	{
		$this->methodCalls[] = 'authorize';
		
		return $this->id > 10;
	}
	
	public function handle($id)
	{
		return $id;
	}
}