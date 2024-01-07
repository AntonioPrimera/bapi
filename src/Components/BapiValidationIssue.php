<?php
namespace AntonioPrimera\Bapi\Components;

/**
 * A simple container used to hold data validation issues
 */
class BapiValidationIssue
{
	public function __construct(
		public readonly string          $attributeName,		//name of the attribute which failed validation
		public readonly mixed           $attributeValue,	//value of the attribute which failed validation
		public readonly string          $errorMessage,		//validation error message
		public readonly string|int|null $errorCode = null	//optional error code as string/int
	)
	{
	}
}