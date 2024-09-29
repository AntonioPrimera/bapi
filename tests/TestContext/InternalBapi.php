<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;

class InternalBapi extends Bapi
{
	public array $callStack = [];
	
	protected function authorize(): true
	{
		$this->callStack[] = static::class . ':authorize';
		return true;
	}
	
	protected function validate(): true
	{
		$this->callStack[] = static::class . ':validate';
		return true;
	}
	
	protected function handle(int $result): array
	{
		$this->callStack[] = static::class . ':handle-result:' . $result;
		return $this->callStack;
	}
}