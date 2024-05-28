<?php
namespace AntonioPrimera\Bapi\Tests\TestContext;

use AntonioPrimera\Bapi\Bapi;
use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use Exception;

/**
 * @property string $exceptionType
 * @method static string run(string $exceptionType)
 */
class ExceptionRenderTestBapi extends Bapi
{
	const EXCEPTION_TYPE_STRING = 'string';
	const EXCEPTION_TYPE_STRING_ARRAY = 'string_array';
	const EXCEPTION_TYPE_BAPI_VALIDATION_ISSUE = 'bvi';
	const EXCEPTION_TYPE_BAPI_VALIDATION_ISSUE_ARRAY = 'bvi_array';
	const EXCEPTION_TYPE_MIXED = 'mixed';
	const EXCEPTION_TYPE_OBJECT_WITH_RENDER_METHOD = 'object_with_render_method';
	
	protected function validate(): mixed
	{
		if ($this->exceptionType === static::EXCEPTION_TYPE_STRING)
			throw new BapiValidationException('string error message');
		
		if ($this->exceptionType === static::EXCEPTION_TYPE_STRING_ARRAY)
			throw new BapiValidationException(['string error message 1', 'string error message 2']);
		
		if ($this->exceptionType === static::EXCEPTION_TYPE_BAPI_VALIDATION_ISSUE)
			return new BapiValidationIssue('att1', 'att1Value', 'att1 error message', 'att1-error-code');
		
		if ($this->exceptionType === static::EXCEPTION_TYPE_BAPI_VALIDATION_ISSUE_ARRAY)
			return [
				new BapiValidationIssue('att1', 'att1Value', 'att1 error message', 'att1-error-code'),
				new BapiValidationIssue('att2', 'att2Value', 'att2 error message', 'att2-error-code'),
			];
		
		if ($this->exceptionType === static::EXCEPTION_TYPE_MIXED)
			return [
				'Error message 1',
				'Error message 2',
				new BapiValidationIssue('att3', 'att3Value', 'att3 error message', 'att3-error-code'),
				new BapiValidationIssue('att4', 'att4Value', 'att4 error message', 'att4-error-code'),
			];
		
		if ($this->exceptionType === static::EXCEPTION_TYPE_OBJECT_WITH_RENDER_METHOD)
			return new ObjectWithRenderMethod();
		
		return true;
	}
	
	protected function handle(string $exceptionType): string
	{
		return 'no exception';
	}
}