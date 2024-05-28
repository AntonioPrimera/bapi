<?php
namespace AntonioPrimera\Bapi\Tests\Unit;

use AntonioPrimera\Bapi\Components\BapiValidationIssue;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use AntonioPrimera\Bapi\Tests\TestContext\ExceptionRenderTestBapi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class BapiValidationExceptionRenderTest extends \AntonioPrimera\Bapi\Tests\TestCase
{
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_single_string_as_an_argument()
	{
		try {
			ExceptionRenderTestBapi::run(exceptionType: ExceptionRenderTestBapi::EXCEPTION_TYPE_STRING);
		} catch (BapiValidationException $exception) {
			$renderedErrors = $exception->render($this->app->make('request'));
			$this->assertInstanceOf(JsonResponse::class, $renderedErrors);
			$this->assertEquals(
				[
					'error' => 'BusinessValidationError',
					'message' => 'string error message',
					'validation_errors' => [['type' => 'generic', 'message' => 'string error message']]
				],
				$renderedErrors->getData(true)
			);
		}
	}
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_list_of_strings_as_an_argument()
	{
		try {
			ExceptionRenderTestBapi::run(exceptionType: ExceptionRenderTestBapi::EXCEPTION_TYPE_STRING_ARRAY);
		} catch (BapiValidationException $exception) {
			$renderedErrors = $exception->render($this->app->make('request'));
			$this->assertInstanceOf(JsonResponse::class, $renderedErrors);
			$this->assertEquals(
				[
					'error' => 'BusinessValidationError',
					'message' => 'string error message 1',
					'validation_errors' => [
						['type' => 'generic', 'message' => 'string error message 1'],
						['type' => 'generic', 'message' => 'string error message 2']
					]
				],
				$renderedErrors->getData(true)
			);
		}
	}
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_single_bapi_validation_issue_as_an_argument()
	{
		try {
			ExceptionRenderTestBapi::run(exceptionType: ExceptionRenderTestBapi::EXCEPTION_TYPE_BAPI_VALIDATION_ISSUE);
		} catch (BapiValidationException $exception) {
			$renderedErrors = $exception->render($this->app->make('request'));
			$this->assertInstanceOf(JsonResponse::class, $renderedErrors);
			$this->assertEquals(
				[
					'error' => 'BusinessValidationError',
					'message' => 'att1 error message',
					'validation_errors' => [
						[
							'type' => 'attribute',
							'message' => 'att1 error message',
							'attribute' => 'att1',
							'value' => 'att1Value',
							'code' => 'att1-error-code'
						]
					]
				],
				$renderedErrors->getData(true)
			);
		}
	}
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_list_of_bapi_validation_issues_as_an_argument()
	{
		try {
			ExceptionRenderTestBapi::run(exceptionType: ExceptionRenderTestBapi::EXCEPTION_TYPE_BAPI_VALIDATION_ISSUE_ARRAY);
		} catch (BapiValidationException $exception) {
			$renderedErrors = $exception->render($this->app->make('request'));
			$this->assertInstanceOf(JsonResponse::class, $renderedErrors);
			$this->assertEquals(
				[
					'error' => 'BusinessValidationError',
					'message' => 'att1 error message',
					'validation_errors' => [
						[
							'type' => 'attribute',
							'message' => 'att1 error message',
							'attribute' => 'att1',
							'value' => 'att1Value',
							'code' => 'att1-error-code'
						],
						[
							'type' => 'attribute',
							'message' => 'att2 error message',
							'attribute' => 'att2',
							'value' => 'att2Value',
							'code' => 'att2-error-code'
						]
					]
				],
				$renderedErrors->getData(true)
			);
		}
	}
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_a_mixed_list_of_strings_and_bapi_validation_issues_as_an_argument()
	{
		try {
			ExceptionRenderTestBapi::run(exceptionType: ExceptionRenderTestBapi::EXCEPTION_TYPE_MIXED);
		} catch (BapiValidationException $exception) {
			$renderedErrors = $exception->render($this->app->make('request'));
			$this->assertInstanceOf(JsonResponse::class, $renderedErrors);
			$this->assertEquals(
				[
					'error' => 'BusinessValidationError',
					'message' => 'Error message 1',	//the first error message should be used as the exception message
					'validation_errors' => [
						['type' => 'generic', 'message' => 'Error message 1'],
						['type' => 'generic', 'message' => 'Error message 2'],
						[
							'type' => 'attribute',
							'message' => 'att3 error message',
							'attribute' => 'att3',
							'value' => 'att3Value',
							'code' => 'att3-error-code'
						],
						[
							'type' => 'attribute',
							'message' => 'att4 error message',
							'attribute' => 'att4',
							'value' => 'att4Value',
							'code' => 'att4-error-code'
						]
					]
				],
				$renderedErrors->getData(true)
			);
		}
	}
	
	#[Test]
	public function a_bapi_validation_exception_can_receive_an_object_with_a_render_method_as_an_argument()
	{
		try {
			ExceptionRenderTestBapi::run(exceptionType: ExceptionRenderTestBapi::EXCEPTION_TYPE_OBJECT_WITH_RENDER_METHOD);
		} catch (BapiValidationException $exception) {
			$renderedErrors = $exception->render($this->app->make('request'));
			$this->assertInstanceOf(JsonResponse::class, $renderedErrors);
			$this->assertEquals(
				[
					'error' => 'BusinessValidationError',
					'message' => '[Unspecified validation error]',
					'validation_errors' => [
						'rendered by object with render method'
					],
				],
				$renderedErrors->getData(true)
			);
		}
	}
}