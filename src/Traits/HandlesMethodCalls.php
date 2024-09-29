<?php
namespace AntonioPrimera\Bapi\Traits;

trait HandlesMethodCalls
{
	/**
	 * Call a method from this instance, with a given set of arguments.
	 * If the method doesn't exist, just return $this.
	 */
	protected function callMethod(string $methodName, ...$params) : mixed
	{
		return method_exists($this, $methodName)
			? $this->{$methodName}(...$params)
			: $this;
	}
}