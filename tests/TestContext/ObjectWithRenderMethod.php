<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

class ObjectWithRenderMethod
{
	public function render(): string
	{
		return 'rendered by object with render method';
	}
}