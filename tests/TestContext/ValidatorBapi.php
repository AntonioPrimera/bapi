<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;
use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use AntonioPrimera\Bapi\Traits\ValidatesAttributes;

/**
 * @property string name
 * @method static string run($name)
 */
class ValidatorBapi extends Bapi
{
	use ValidatesAttributes;
	
	/**
	 * Business data validation
	 */
	protected function validate(): mixed
	{
		if ($this->name === 'BapiValidationIssue')
			throw new BapiValidationException(
				new BapiValidationIssue('name', 'x', 'Name error', 'XNE')
			);
		
		if ($this->name === 'Array')
			throw new BapiValidationException([
				new BapiValidationIssue('name', 'x', 'Name error', 'XNE'),
				new BapiValidationIssue('age', 20, 'Age error', 'XNA'),
				'ignored' => 'Ignored message'
			]);
		
		if ($this->name === 'EmptyArray')
			throw new BapiValidationException([]);
		
		if ($this->name === 'String')
			throw new BapiValidationException('Some error');
		
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
