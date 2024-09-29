<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\InternalBapi;

class ExclusiveInternalBapi extends InternalBapi
{
	public array $callStack = [];
	
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