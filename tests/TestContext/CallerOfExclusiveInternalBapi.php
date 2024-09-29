<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;

/**
 * @method static array run(int $value, int $multiplier = 2)
 */
class CallerOfExclusiveInternalBapi extends Bapi
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
	
	protected function handle(int $value, int $multiplier = 2): array
	{
		$this->callStack[] = static::class . ':handle';
		$internalCallResult = ExclusiveInternalBapi::call(result: $value * $multiplier);
		return array_merge($this->callStack, $internalCallResult);
	}
}