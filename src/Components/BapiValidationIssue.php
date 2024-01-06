<?php
namespace AntonioPrimera\Bapi\Components;

/**
 * A simple container used to hold data validation issues
 */
class BapiValidationIssue
{
	public function __construct(
		public readonly string $attribute,	//name of the attribute which failed validation
		public readonly mixed $value,		//value of the attribute which failed validation
		public readonly string $error,		//validation error in plain text, translation key or an error key
		public readonly string|int|null $errorCode = null	//optionally, a unique error code can be added as string/int
	)
	{
	}
}