<?php
namespace AntonioPrimera\Bapi\Exceptions;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

class BapiValidationException extends BapiException
{
	public readonly mixed $validationErrors;
	
	public function __construct(mixed $validationErrors = null, $message = "", $code = 0, Throwable $previous = null)
	{
		$this->validationErrors = $validationErrors;
		parent::__construct($this->determineMessageFromValidationErrors($message), $code, $previous);
	}
	
	public function render(Request $request)
	{
		//if a custom renderer is set, use it
		$renderer = config('bapi.validationExceptionRenderer');
		if ($renderer && is_callable($renderer))
			return call_user_func($renderer, $this, $request);
		
		//default to json response
		return $this->renderJsonResponse($request);
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	/**
	 * Determine the exception message from the validation errors
	 */
	public function determineMessageFromValidationErrors(string $defaultMessage)
	{
		$firstError = Collection::wrap($this->validationErrors)->first();
		
		//if a single validation issue was passed, use its error message
		if ($firstError instanceof BapiValidationIssue)
			return $firstError->errorMessage;
		
		//if a string was passed, use it as the error message
		if (is_string($firstError))
			return $firstError;
		
		return $defaultMessage ?: '[Unspecified validation error]';
	}
	
	//--- Public render helpers ---------------------------------------------------------------------------------------
	
	public function renderJsonResponse(Request $request): JsonResponse
	{
		return response()->json([
			'error' => 'BusinessValidationError',
			'message' => $this->getMessage(),
			'validation_errors' => $this->renderValidationErrors($request)
		], 409);
	}
	
	public function renderValidationErrors(Request $request): array
	{
		return Collection::wrap($this->validationErrors)
			->map(fn($error) => $this->renderValidationError($error, $request))
			->filter()
			->values()
			->toArray();
	}
	
	public function renderValidationError(mixed $error, Request $request): mixed
	{
		//if the validation errors are a string, return it as a single-element array
		if (is_string($error))
			return $this->renderStringIssue($error);
		
		//if the validation errors is a single BapiValidationIssue, return it as a single-element array
		if ($error instanceof BapiValidationIssue)
			return $this->renderBapiValidationIssue($error);
		
		//if the validation error has a render method, call it
		if (is_callable([$error, 'render']))
			return $error->render($request);
		
		return null;
	}
	
	public function renderStringIssue(string $issue): array
	{
		return [
			'type' => 'generic',
			'message' => $issue
		];
	}
	
	public function renderBapiValidationIssue(BapiValidationIssue $bapiValidationIssue): array
	{
		return [
			'type' => 'attribute',
			'message' => $bapiValidationIssue->errorMessage,
			'attribute' => $bapiValidationIssue->attributeName,
			'value' => $bapiValidationIssue->attributeValue,
			'code' => $bapiValidationIssue->errorCode
		];
	}
}