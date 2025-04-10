<?php
namespace AntonioPrimera\Bapi\Console\Commands\Support;

use AntonioPrimera\Bapi\BaseBapi;
use AntonioPrimera\Bapi\InternalBapi;
use AntonioPrimera\FileSystem\File;
use Illuminate\Support\Collection;

class BapiClassDescription
{
	//class description properties
	public string|null $namespace = null;
	public string|null $className = null;
	public string|null $fqn = null;
	public bool $isBapiClass = false;
	public bool $isInternalBapi = false;
	public Collection|null $handleMethodParameters = null;
	public string|null $handleMethodReturnType = null;
	//public \stdClass|null $handleMethod = null;
	public array $includes = [];
	
	//doc block properties
	public array $docBlockProps = [];
	public array $docBlockMethods = [];
	
	//--- Construction and Factories ----------------------------------------------------------------------------------
	
	public function __construct(public readonly File $file, public bool $useIncludes = true)
	{
		$this->setup($file);
	}
	
	public static function fromFile(File $file): self|null
	{
		return new self($file);
	}
	
	//set up the properties of the class (on instantiation)
	protected function setup(File $file): void
	{
		$this->namespace = $this->getNamespace($file->contents);
		$this->className = $this->getClassName($file->contents);
		$this->isBapiClass = $this->isBapiClass($file, $this->namespace, $this->className);
		
		//if this is not a bapi class, we don't need to determine the rest of the properties
		if (!$this->isBapiClass)
			return;
		
		$this->fqn = collect([$this->namespace, $this->className])->filter()->join('\\');
		$this->isInternalBapi = is_subclass_of($this->fqn, InternalBapi::class);
		$this->handleMethodParameters = $this->determineHandleMethodParameters($this->fqn);
		$this->handleMethodReturnType = $this->determineHandleMethodReturnType($this->fqn);
		
		//add doc block methods (call and callStatic for internal and normal bapis)
		$this->addDocBlockMethod('callStatic', 'call', $this->handleMethodParameters, $this->handleMethodReturnType, true)
			->addDocBlockMethod('call', 'call', $this->handleMethodParameters, $this->handleMethodReturnType, false);
		
		//add run methods if this is not an internal bapi
		if (!$this->isInternalBapi)
			$this->addDocBlockMethod('runStatic','run', $this->handleMethodParameters, $this->handleMethodReturnType, true)
				->addDocBlockMethod('run', 'run', $this->handleMethodParameters, $this->handleMethodReturnType, false);
	}
	
	//--- Public API --------------------------------------------------------------------------------------------------
	
	public function helperCode(int $indent = 4): string
	{
		$docBlock = $this->generateDocBlock($indent);
		$indentString = str_repeat(' ', $indent);
		
		return "$docBlock\n{$indentString}class $this->className {}\n";
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function isBapiClass(File $file, string|null $namespace, string|null $className): bool
	{
		return $namespace
			&& $className
			&& $file->nameWithoutExtension === $className
			&& is_subclass_of("$namespace\\$className", BaseBapi::class);
	}
	
	protected function getNamespace(string $fileContents): string|null
	{
		preg_match('/namespace\s+([\w\\\\]+)\s*;/', $fileContents, $matches);
		return $matches[1] ?? null;
	}
	
	protected function getClassName(string $fileContents): string|null
	{
		preg_match('/class\s+(\w+)\s+/', $fileContents, $matches);
		return $matches[1] ?? null;
	}
	
	protected function determineHandleMethodParameters(string $fqn): Collection
	{
		$reflection = new \ReflectionMethod(new $fqn, 'handle');
		return collect($reflection->getParameters())
			->map(fn(\ReflectionParameter $parameter) => $this->parameterString($parameter));
	}
	
	protected function determineHandleMethodReturnType(string $fqn): string
	{
		$reflection = new \ReflectionMethod(new $fqn, 'handle');
		return $this->typeString($reflection->getReturnType());
	}
	
	protected function addInclude(string|\ReflectionNamedType $type): void
	{
		if (is_string($type)) {
			$this->includes[$type] = class_basename($type);
			return;
		}
		
		if (!$type->isBuiltin())
			$this->includes[$type->getName()] = class_basename($type->getName());
	}
	
	//--- DocBlock generation -----------------------------------------------------------------------------------------
	
	protected function generateDocBlock(int $indentSpaces = 4): string
	{
		$docBlock = new DocBlock(null, $indentSpaces);
		
		foreach ($this->docBlockProps as $docBlockProp)
			$docBlock->addProperty($docBlockProp->type, $docBlockProp->name, $docBlockProp->description);
		
		foreach ($this->docBlockMethods as $method)
			$docBlock->addMethod($method->name, $method->parameterString, $method->returnTypeString, $method->static);
		
		return $docBlock->contents;
	}
	
	protected function addDocBlockProperty(string $name, string $type, string $description = ''): static
	{
		$this->docBlockProps[] = (object) [
			'name' => $name,
			'type' => $type,
			'description' => $description,
		];
		
		return $this;
	}
	
	protected function addDocBlockMethod(string $key, string $methodName, Collection $parameters, string $returnTypeString, bool $static): static
	{
		$this->docBlockMethods[$key] = (object) [
			'static' => $static,
			'name' => $methodName,
			'parameterString' => $parameters->implode(', '),
			'returnTypeString' => $returnTypeString,
		];
		
		return $this;
	}
	
	//--- Reflection helpers ------------------------------------------------------------------------------------------
	
	protected function parameterString(\ReflectionParameter $parameter): string
	{
		$type = $parameter->hasType() ? $this->typeString($parameter->getType()) : '';
		$name = '$' . $parameter->getName();
		$default = $parameter->isDefaultValueAvailable() ? '= ' . $this->valueString($parameter->getDefaultValue()) : '';
		
		return collect([$type, $name, $default])->filter()->implode(' ');
	}
	
	protected function typeString(\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type): string
	{
		if ($type instanceof \ReflectionNamedType){
			$this->addInclude($type);
			$typeString = $type->isBuiltin()
				? $type->getName()
				: ($this->useIncludes ? $this->includes[$type->getName()] : '\\' . $type->getName());
			
			return $type->allowsNull() ? "$typeString|null" : $typeString;
		}
		
		if ($type instanceof \ReflectionUnionType)
			return collect($type->getTypes())->map(fn(\ReflectionNamedType $type) => $this->typeString($type))->implode('|');
		
		if ($type instanceof \ReflectionIntersectionType)
			return collect($type->getTypes())->map(fn(\ReflectionNamedType $type) => $this->typeString($type))->implode('&');
		
		return '';
	}
	
	protected function valueString(mixed $value): string
	{
		if ($value instanceof \BackedEnum) {
			$this->addInclude(get_class($value));
			return ($this->useIncludes ? class_basename($value) : '\\' . get_class($value))
				. '::' . $value->name;
		}
		
		if (is_string($value))
			return "'$value'";
		
		if (is_array($value))
			return '['. collect($value)->map(fn($value, $key) => "'$key' => " . $this->valueString($value))->implode(', ') . ']';
		
		if (is_null($value))
			return 'null';
		
		if (is_bool($value))
			return $value ? 'true' : 'false';
		
		return (string) $value;
	}
}
