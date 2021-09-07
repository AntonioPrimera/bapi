<?php
namespace AntonioPrimera\Bapi\Traits;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;

trait HandlesExceptions
{
	
	/**
	 * List of exceptions which should be thrown by the
	 * Bapi, without any exception handling
	 *
	 * @var string[]
	 */
    protected array $throw = [
        AuthorizationException::class,
        BapiException::class,
    ];
    
    /**
     * If the given exception is one of the exception types listed in
     * $this->throw, then just throw it. All other exceptions
     * will pass through and don't have any effect.
     *
     * @param Exception $exception
     *
     * @throws Exception
     */
    protected function throwAcceptableExceptions(Exception $exception)
    {
        foreach ($this->throw as $exceptionType) {
            if (is_a($exception, $exceptionType))
                throw $exception;
        }
    }
	
	/**
	 * Public hook - use this to do any specific exception handling
	 *
	 * @param Exception $exception
	 *
	 * @throws Exception
	 */
    protected function handleException(Exception $exception)
    {
        throw $exception;
    }
}