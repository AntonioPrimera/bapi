<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;
use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;

/**
 * @property string name
 * @method static string run($name)
 */
class CreateCompanyBapi extends Bapi
{
	
	/**
	 * Business data validation
	 */
	protected function validate(): mixed
	{
		if ($this->name === 'Exception')
			throw new BapiValidationException('Specific errors');
		
		if ($this->name === 'Issue')
			return new BapiValidationIssue('name', $this->name, 'Some error');
		
		if ($this->name === 'Array')
			return [new BapiValidationIssue('name', $this->name, 'Array')];
		
		if ($this->name === 'False')
			return false;
		
		if ($this->name === 'True')
			return true;
		
		if ($this->name === 'EmptyArray')
			return [];
		
		return true;
	}
	
	protected function authorize(): bool
	{
		return true;
	}
	
	public function handle($name)
	{
		return $name;
	}
}
