<?php
namespace AntonioPrimera\Bapi;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use AntonioPrimera\Bapi\Traits\HandlesAuthorization;
use AntonioPrimera\Bapi\Traits\HandlesExceptions;
use AntonioPrimera\Bapi\Traits\UsesDbTransactions;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * @method static static withoutAuthorizationCheck()
 * @method static static withAuthorizationCheck()
 * @method static static withoutDbTransaction()
 * @method static static withDbTransaction()
 */
abstract class Bapi extends BaseBapi
{
    use HandlesAuthorization, UsesDbTransactions, HandlesExceptions;
	
    //--- Magic stuff -------------------------------------------------------------------------------------------------
	
	/**
	 * @throws BapiException
	 * @throws Exception
	 */
	public function __call($method, $params) : mixed
    {
		return match ($method) {
			'run' => $this->handleRun(...$params),
			'withAuthorizationCheck' => $this->setAuthorizationCheck(true),
			'withoutAuthorizationCheck' => $this->setAuthorizationCheck(false),
			'withDbTransaction' => $this->setDbTransaction(true),
			'withoutDbTransaction' => $this->setDbTransaction(false),
			default => throw new BapiException(sprintf(
				'Method %s::%s does not exist.', static::class, $method
			))
		};
	}
	
	/**
	 * @throws BapiException
	 */
	public static function __callStatic($method, $params) : mixed
    {
		//the "call" method is reserved for internal bapi calls and should not be used outside a bapi
		if ($method === 'call')
			throw new BapiException('Cannot use internal bapi call outside bapis', 0);
		
        //any other call will be forwarded to a newly instanced bapi
        return (new static)->$method(...$params);
    }
	
	/**
	 * The bapi can be invoked (instance used as a function), giving
	 * it the parameters expected by the handle method
	 *
	 * @throws Exception
	 */
    public function __invoke(...$args) : mixed
    {
        return $this->handleRun(...$args);
    }
	
	/**
	 * Use this method when calling a bapi internally from another bapi,
	 * in order to skip DB Transaction and the authorization check.
	 */
	protected static function call(...$args): mixed
	{
		$instance = new static;
		$instance->withAuthorizationCheck = false;
		return $instance->handleInternalRun(...$args);
	}
	
	/**
	 * Use this method when calling a bapi internally from another bapi,
	 * without a DB Transaction, but with authorization check.
	 *
	 * @throws Exception
	 */
	protected static function callWithAuthorizationCheck(...$args): mixed
	{
		return (new static)->handleInternalRun(...$args);
	}
    
    //--- Setup and run preparation -----------------------------------------------------------------------------------
	
	/**
	 * Run the bapi, wrapped in a DB Transaction, so that changes to
	 * the DB are committed only if everything went well. If
	 * any exception was thrown, roll back everything
	 *
	 * @throws Exception
	 */
	protected function handleRun(...$args)
    {
        try {
			if ($this->useDbTransaction)
            	DB::beginTransaction();
    
            //take the attributes from the "run" method (static / instance) and match them with
			//the arguments of the "handle" method. The "run" method must use named arguments
            $this->fillArguments(...$args);
			
			//run the authorization check
			$this->handleAuthorization();
			
            //run the business validation check (only if the authorization check was successful)
			$this->handleValidation();
			
            //call the "handle" method, which should contain the actual business logic of the bapi
            $result = $this->callMethod('handle', ...$args);
            
            //run the result through the "afterHandle" hook, for any necessary data transformation or cleanup
            $finalResult = $this->processResult($result);										  //Hook: processResult
            
			if($this->useDbTransaction)
            	DB::commit();
            
            return $finalResult;
        } catch (Exception $exception) {
        	//in case of any exception, roll back any changes to the DB
			if($this->useDbTransaction)
            	DB::rollBack();
            
            //if the exception is one listed in $this->throw, then just throw the exception ...
            $this->throwAcceptableExceptions($exception);
            
            //... otherwise run the exception handler
            return $this->handleException($exception);
        }
    }
	
	/**
	 * Handle running the bapi, when called by another bapi (internal run)
	 * No DB transaction will be used and by default no authorization
	 * check will be done, unless used "withAuthorizationCheck"
	 *
	 * @throws Exception
	 */
	protected function handleInternalRun(...$args): mixed
	{
		try {
			//take the attributes from the "run" method (static / instance) and match them with
			//the arguments of the "handle" method. The "run" method must use named arguments
			$this->fillArguments(...$args);
			
			//run the authorization check if specifically requested
			$this->handleAuthorization();
			
			//run the business validation check
			$this->handleValidation();
			
			//call the "handle" method, which should contain the actual business logic of the bapi
			$result = $this->callMethod('handle', ...$args);
			
			//run the result through the "afterHandle" hook, for any necessary data transformation or cleanup
			return $this->processResult($result);										  //Hook: processResult
		} catch (Exception $exception) {
			//if the exception is one listed in $this->throw, then just throw the exception ...
			$this->throwAcceptableExceptions($exception);
			
			//... otherwise run the exception handler
			return $this->handleException($exception);
		}
	}
}