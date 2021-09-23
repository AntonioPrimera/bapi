<?php

namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;
use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;


class CreateCompanyBapi extends Bapi
{
	
	/**
	 * Business data validation
	 *
	 * @return bool | iterable | BapiValidationIssue
	 * @throws BapiValidationException
	 */
	protected function validate(): bool|iterable|BapiValidationIssue
	{
		if ($this->name === 'Exception')
			throw new BapiValidationException();
		
		if ($this->name === 'Issue')
			return new BapiValidationIssue('name', $this->name, 'Issue');
		
		if ($this->name === 'Array')
			return [new BapiValidationIssue('name', $this->name, 'Array')];
		
		if ($this->name === 'Collection')
			return collect([new BapiValidationIssue('name', $this->name, 'Collection')]);
		
		if ($this->name === 'False')
			return false;
		
		if ($this->name === 'True')
			return true;
		
		if ($this->name === 'EmptyArray')
			return [];
		
		if ($this->name === 'EmptyCollection')
			return collect();
		
		return true;
	}
	
	/**
	 * Authorization check
	 *
	 * @return bool
	 */
	protected function authorize(): bool
	{
		return true;
	}
	
	/**
	 * Test Bapi
	 */
	public function handle($name)
	{
		return $name;
	}
}
