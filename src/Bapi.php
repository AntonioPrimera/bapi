<?php /** @noinspection PhpVoidFunctionResultUsedInspection */
namespace AntonioPrimera\Bapi;

use AntonioPrimera\Bapi\Exceptions\BapiException;
use AntonioPrimera\Bapi\Traits\HandlesAttributes;
use AntonioPrimera\Bapi\Traits\HandlesAuthorization;
use AntonioPrimera\Bapi\Traits\HandlesExceptions;
use AntonioPrimera\Bapi\Traits\HandlesValidation;
use Exception;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;

/**
 * @method static static withoutAuthorizationCheck()
 */
class Bapi
{
    use HandlesValidation, HandlesAttributes, HandlesAuthorization, HandlesExceptions;
	
    public function __construct()
    {
		//make sure the bapi has a "handle" method
		if (!method_exists($this, 'handle'))
			throw new BapiException('Bapi must have a "handle" method', 0);
    }
    
    //--- Magic stuff -------------------------------------------------------------------------------------------------
	
	public function __call($method, $params) : mixed
    {
		return match ($method) {
			'run' => $this->handleRun(...$params),
			'withAuthorizationCheck' => $this->setAuthorizationCheck(true),
			'withoutAuthorizationCheck' => $this->setAuthorizationCheck(false),
			default => throw new Exception(sprintf(
				'Method %s::%s does not exist.', static::class, $method
			))
		};
	}
	
	public static function __callStatic($method, $params) : mixed
    {
        //any call will be forwarded to a newly instanced bapi
        return (new static)->$method(...$params);
    }
	
	/**
	 * The bapi can be invoked (instance used as a function), giving
	 * it the parameters expected by the handle method
	 */
    public function __invoke(...$args) : mixed
    {
        return $this->handleRun(...$args);
    }
    
    //--- Setup and run preparation -----------------------------------------------------------------------------------
	
	/**
	 * Call a method from this instance, with a given set of arguments.
	 * If the method doesn't exist, just return $this.
	 */
    protected function callMethod(string $methodName, ...$params) : mixed
    {
        return method_exists($this, $methodName)
            ? $this->{$methodName}(...$params)
            : $this;
    }
	
	/**
	 * Uses reflection to match the provided named arguments to the arguments of the 'handle' method
	 * and saves their values into the attribute set of the bapi (accessible via magic methods)
	 */
    protected function fillArguments(...$args) : static
    {
        if (!method_exists($this, 'handle'))
			throw new BapiException('Bapi must have a "handle" method', 0);
		
		//run through all parameters of the handle method and set the corresponding attribute to the value of
		//the argument with the same name (or the default value, if the argument was not provided)
		foreach((new ReflectionMethod($this, 'handle'))->getParameters() as $param) {
			$paramName = $param->getName();
			$defaultValue = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
			$this->attributes[$paramName] = $args[$paramName] ?? $defaultValue;
		}
        
        return $this;
    }
	
	/**
	 * Run the bapi, wrapped in a DB Transaction, so that changes to
	 * the DB are committed only if everything went well. If
	 * any exception was thrown, roll back everything
	 */
	protected function handleRun(...$args)
    {
        try {
            DB::beginTransaction();
    
            //take the attributes from the "run" method (static / instance) and match them with
			//the arguments of the "handle" method. The "run" method must use named arguments
            $this->fillArguments(...$args);
			
			//run the authorization check if needed
			if ($this->withAuthorizationCheck)
				$this->handleAuthorization();
			
            //run the business validation check (only if the authorization check was successful)
			$this->handleValidation();
			
            //call the "handle" method, which should contain the actual business logic of the bapi
            $result = $this->callMethod('handle', ...$args);
            
            //run the result through the "afterHandle" hook, for any necessary data transformation or cleanup
            $finalResult = $this->processResult($result);										  //Hook: processResult
            
            DB::commit();
            
            return $finalResult;
        } catch (Exception $exception) {
        	//in case of any exception, roll back any changes to the DB
            DB::rollBack();
            
            //if the exception is one listed in $this->throw, then just throw the exception ...
            $this->throwAcceptableExceptions($exception);
            
            //... otherwise run the exception handler
            return $this->handleException($exception);
        }
    }
	
    //--- Hooks -------------------------------------------------------------------------------------------------------
	
	/**
	 * This hook receives the data resulted from the "handle" method, right before
	 * it is returned to the context where the bapi was called. Use this if
	 * any cleanup or transformation of the data needs to be done
	 */
    protected function processResult(mixed $result) : mixed
    {
        return $result;
    }
}