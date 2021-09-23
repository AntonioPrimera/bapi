<?php

namespace AntonioPrimera\Bapi\Components;

/**
 * A simple container used to respond to data validation. It returns the attribute which was at fault, the
 * value of the attribute, the error as a string (translation key / error message / error key) and
 * optionally an error code. The class exposes the attributes as read-only, and uses setters
 * for changing these attributes, in order to enforce type validation on the values.
 *
 * @property string $attribute
 * @property string $error
 * @property string|int|null $errorCode
 * @property mixed $value
 */
class BapiValidationIssue
{
	
	/**
	 * The name of the attribute which
	 * failed the validation
	 *
	 * @var string
	 */
	protected string $attribute;
	
	/**
	 * The value of the attribute which
	 * failed the validation
	 *
	 * @var mixed
	 */
	protected mixed $value = null;
	
	/**
	 * The validation error in plain text,
	 * translation key or an error key
	 *
	 * @var string
	 */
	protected string $error;
	
	/**
	 * Optionally, a unique error code
	 * can be added as string/int
	 *
	 * @var string|int|null
	 */
	protected string|int|null $errorCode = null;
	
	
	public function __construct(string $attribute, mixed $value, string $error, string|int|null $errorCode = null)
	{
		
		$this->attribute = $attribute;
		$this->value = $value;
		$this->error = $error;
		$this->errorCode = $errorCode;
	}
	
	//--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __get($name)
	{
		//expose the protected attributes as read-only attributes
		if (in_array($name, ['attribute', 'error', 'errorCode', 'value'])) {
			return $this->$name;
		}
		
		return null;
	}
	
	//--- Setters -----------------------------------------------------------------------------------------------------
	
	/**
	 * @param string $attribute
	 */
	public function setAttribute(string $attribute): void
	{
		$this->attribute = $attribute;
	}
	
	/**
	 * @param string $error
	 */
	public function setError(string $error): void
	{
		$this->error = $error;
	}
	
	/**
	 * @param int|string|null $errorCode
	 */
	public function setErrorCode(int|string|null $errorCode): void
	{
		$this->errorCode = $errorCode;
	}
	
	/**
	 * @param mixed|null $value
	 */
	public function setValue(mixed $value): void
	{
		$this->value = $value;
	}
}