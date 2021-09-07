<?php /** @noinspection PhpVoidFunctionResultUsedInspection */
namespace AntonioPrimera\Bapi;

use AntonioPrimera\Bapi\Traits\HandlesAttributes;
use AntonioPrimera\Bapi\Traits\HandlesAuthorizationCheck;
use AntonioPrimera\Bapi\Traits\HandlesExceptions;
use AntonioPrimera\Bapi\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;

/**
 * @method mixed run(...$mixed)
 * @method self withAuthorizationCheck()
 * @method self withoutAuthorizationCheck()
 */
class Bapi
{
    use HandlesAttributes, HandlesAuthorizationCheck, HandlesValidation, HandlesExceptions;
    
    protected Authenticatable | null $actingAs;
    
    public function __construct()
    {
    	//call the setup method, if it exists
        $this->callMethod('setup');
        
        //take the arguments given to the constructor and store them into the attributes array of the bapi
		//the keys (names) of the attributes will be determined via Reflection from the "handle" method
		//make sure to instantiate the bapi with exactly the same arguments as the "handle" method
        $args = func_num_args() > 0 ? func_get_args() : [];
        $this->resolveAttributes(...$args);
    }
    
    //--- Magic stuff -------------------------------------------------------------------------------------------------
	
	/**
	 * @throws Exception
	 */
	public function __call($method, $params)
    {
    	//you can call "run" ...
        if ($method === 'run') {
            return $this->handleRun($params);
        }
		
        //... or change the authorizationCheck flag ...
        if (in_array($method, ['withAuthorizationCheck', 'withoutAuthorizationCheck'])) {
            return $this->{'_' . $method}(...$params);
        }
        
        //... anything else will throw an exception
        throw new Exception(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
	
	/**
	 * @throws Exception
	 */
	public static function __callStatic($method, $params)
    {
    	//you can call "run" statically
        if ($method === 'run') {
            return (new static())->handleRun($params);
        }
        
        //any other call will be forwarded to a newly instanced bapi
        return (new static)->$method(...$params);
    }
    
    public function __invoke(array $attributes = [])
    {
        return $this->run($attributes);
    }
    
    //--- Setup and run preparation -----------------------------------------------------------------------------------
	
	/**
	 * Call a method from this instance, with a given set of arguments.
	 * If the method doesn't exist, just return $this.
	 *
	 * @param       $methodName
	 * @param array $params
	 *
	 * @return mixed
	 */
    protected function callMethod($methodName, array $params = []) : mixed
    {
        return method_exists($this, $methodName)
            ? $this->{$methodName}(...$params)
            : $this;
    }
	
	/**
	 * Uses reflection to determine the arguments of the "handle" method, maps
	 * the given parameters to the arguments of the "handle" method and
	 * saves the values into the attribute list of this instance
	 *
	 * @param ...$args
	 *
	 * @return $this
	 */
    protected function resolveAttributes(...$args) : static
    {
        if (method_exists($this, 'handle')) {
        	//reflect on the handle method
            $reflector = new ReflectionMethod($this, 'handle');
            
            //add all given arguments to the attribute list, using as keys the names of the
			//attributes at the same index in the "handle" method signature
            foreach($reflector->getParameters() as $index => $param) {
                $defaultValue = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                $this->set($param->getName(), Arr::get($args, $index, $defaultValue));
            }
        }
        
        return $this;
    }
	
	/**
	 * Run the bapi, wrapped in a DB Transaction, so that changes to
	 * the DB are committed only if everything went well. If
	 * any exception was thrown, roll back everything
	 *
	 * @throws Exception
	 */
	protected function handleRun(array $attributes = [])
    {
        try {
            DB::beginTransaction();
    
            //take the attributes from the "run" method (static / instance) and fill
			//them into the existing attribute list, based on their position
			//make sure to call "run" with the same attributes as the "handle" method
            $this->fillValues($attributes);
			
            //do the business validations and data preparation
            $this->handleValidationAndDataPreparation();	//methods: validateData & prepareData (in this order)
            
            //run the authorization check if needed
            if ($this->withAuthorizationCheck) {
                $this->beforeAuthorization();	//public hook: 	beforeAuthorization
                $this->handleAuthorization();	//method: 		authorize
                $this->afterAuthorization();	//public hook: 	afterAuthorization
            }
			
            //run the beforeHandle hook
            $this->beforeHandle();
            
            //call the "handle" method, which should contain the actual business logic of the bapi
            $result = $this->callMethod('handle', $attributes);
            
            //run the result through the "afterHandle" hook, for any necessary data transformation or cleanup
            $finalResult = $this->afterHandle($result);	//public hook: afterHandle
            
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
    
    //--- Public methods ----------------------------------------------------------------------------------------------
	
	/**
	 * Change the actor running this bapi. By default, the bapi is run by the
	 * currently authenticated user, but this can be changed by calling
	 * this method and providing another Authenticatable instance
	 *
	 * @param Authenticatable $user
	 *
	 * @return $this
	 */
    public function actingAs(Authenticatable $user) : static
    {
        $this->actingAs = $user;
        
        return $this;
    }
	
	/**
	 * Return the actor for the current bapi. If not specified
	 * using the actingAs method, the actor/user which is
	 * currently authenticated will be returned
	 *
	 * @return Authenticatable|null
	 */
    public function actor() : Authenticatable | null
    {
        return $this->actingAs ?? auth()->user();
    }
    
    //--- Public hooks ------------------------------------------------------------------------------------------------
	
	/**
	 * Public hook - use this in case you need to prepare data
	 * for the authorization process, so you can keep
	 * the authorize method simple and clean
	 */
    protected function beforeAuthorization()
    {
    }
	
	/**
	 * Public hook - use this if you need to do some costly or sensitive
	 * data preparation, logging or other tasks only if the user is
	 * authorized and the bapi logic should actually run
	 */
    protected function afterAuthorization()
    {
    }
	
	/**
	 * Public hook - this will be run right before the handle method is called
	 */
    protected function beforeHandle()
    {
    }
	
	/**
	 * Public hook - receives the data resulted from the "handle" method, right before
	 * it is returned to the context where the bapi was called. Use this if
	 * any cleanup or transformation of the data needs to be done
	 *
	 * @param $result
	 *
	 * @return mixed
	 */
    protected function afterHandle($result) : mixed
    {
        return $result;
    }
    
    //protected function handleException(\Exception $exception)
    //{
    //    throw $exception;
    //}
}