<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use AntonioPrimera\Bapi\Exceptions\BapiValidationException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;

trait HandlesExceptions
{
	
	/**
	 * Return the list of exceptions which should be thrown by the
	 * Bapi, without any interference. All other exceptions
	 * will be handled by the handleException method.
	 */
	protected function throwableExceptions(): array
	{
		return [
			AuthorizationException::class,
			BapiException::class,
			BapiValidationException::class,
		];
	}
    
    /**
     * If the given exception is one of the exception types listed in
     * $this->throw, then just throw it. All other exceptions
     * will pass through and don't have any effect.
     */
    protected function throwAcceptableExceptions(Exception $exception): void
    {
        foreach ($this->throwableExceptions() as $exceptionType) {
            if (is_a($exception, $exceptionType))
                throw $exception;
        }
    }
	
	/**
	 * Public hook - use this to do any specific exception handling
	 */
    protected function handleException(Exception $exception)
    {
        throw $exception;
    }
}