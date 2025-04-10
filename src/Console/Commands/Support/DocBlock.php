<?php
namespace AntonioPrimera\Bapi\Console\Commands\Support;

use AntonioPrimera\FileSystem\File;
use Illuminate\Support\Collection;

class DocBlock
{
	public string $contents;
	protected string $indent = '';
	
	public function __construct(bool|string|null $contents = null, int|string $indent = 0)
	{
		$this->contents = $contents ?: '';
		$this->indent = $contents
			? $this->determineIndent()
			: (is_int($indent) ? str_repeat(' ', $indent) : $indent);
		
		if (!$contents)
			$this->contents = "$this->indent/**\n$this->indent */";
	}
	
	public static function fromClass(string $className): self
	{
		$classReflector = new \ReflectionClass($className);
		return new self($classReflector->getDocComment());
	}
	
	public function filePosition(string|File $file): \stdClass
	{
		$fileInstance = File::instance($file);
		$hasDocBlock = str_contains($fileInstance->contents, $this->contents);
		$startIndex = $hasDocBlock ? strpos($fileInstance->contents, $this->contents) : strpos($fileInstance->contents, "class ");
		$length = $hasDocBlock ? strlen($this->contents) : 0;
		$endIndex = $startIndex + $length;
		
		return (object) [
			'file' => $fileInstance,
			'hasDocBlock' => $hasDocBlock,
			'start' => $startIndex,
			'end' => $endIndex,
			'length' => $length,
		];
	}
	
	public function replaceInFile(\stdClass $filePosition, bool $dryRun = false): static
	{
		$contents = substr($filePosition->file->contents, 0, $filePosition->start)
			. $this->contents
			. ($filePosition->hasDocBlock ? '' : "\n")
			. substr($filePosition->file->contents, $filePosition->end);
		
		if (!$dryRun)
			$this->writeFile($filePosition->file, $contents, !$filePosition->hasDocBlock);
		
		if ($dryRun)
			ray($contents)->label('New file contents');
		
		return $this;
	}
	
	public function process(): void
	{
		$this->clearProperties();
		//ray($this->contents)->label('Cleared properties');
		
		$this->clearMethods();
		//ray($this->contents)->label('Cleared methods');
		
		$this->addProperty('string', 'name', 'The name of the user');
		//ray($this->contents)->label('Added property');
		
		$this->addMethod('run', 'string $name', '\App\Models\User', true);
		//ray($this->contents)->label('Added method');
	}
	
	//--- Public API --------------------------------------------------------------------------------------------------
	
	public function clearProperties(): static
	{
		return $this->clearLines(fn(string $line) => $this->lineIsProperty($line));
	}
	
	public function clearMethods(): static
	{
		return $this->clearLines(fn(string $line) => $this->lineIsMethod($line));
	}
	
	public function addPropertySignature(string $signature): static
	{
		return $this->append("@property $signature");
	}
	
	public function addProperty(string $type, string $name, string $description = ''): static
	{
		return $this->append(collect(["@property", $type, "\$$name", $description])->filter()->join(' '));
	}
	
	public function addMethodSignature(string $signature): static
	{
		return $this->append("@method $signature");
	}
	
	public function addMethod(string $methodName, string|null $parameterString, string|null $returnType, bool $static): static
	{
		$signature = collect([$static ? 'static' : null, $returnType, "$methodName($parameterString)"])
			->filter()
			->implode(' ');
		return $this->append("@method $signature");
	}
	
	//--- Protected fileSystem helpers --------------------------------------------------------------------------------
	
	protected function writeFile(File $file, string $contents): void
	{
		//prepare the files and keep the original file name
		$filename = $file->name;
		$backupFile = File::instance($file->path . '.backup');
		$tempFile = File::instance($file->path . '.temp');
		
		//delete the backup and temp files if they exist
		if ($backupFile->exists)
			$backupFile->delete();
		
		if ($tempFile->exists)
			$tempFile->delete();
		
		//rename the original file to the backup file and put the new contents in the temp file
		$file->rename($backupFile->name);
		$tempFile->putContents($contents);
		
		//rename the temp file to the original file name
		$tempFile->rename($filename);
		
		//delete the backup file
		$backupFile->delete();
	}
	
	//--- Protected modifiers (modify the $this->contents) ------------------------------------------------------------
	
	/**
	 * Add the given line before the end of the doc block.
	 */
	protected function append(string $line): static
	{
		$lines = $this->lines();
		$lastLine = $lines->pop();
		$this->contents = $lines->push("$this->indent * $line", $lastLine)->join("\n");
		return $this;
	}
	
	/**
	 * Clear all lines that match the given filter.
	 */
	protected function clearLines(callable $filter): static
	{
		$this->contents = $this->lines()->filter(fn(string $line) => !$filter($line))->join("\n");
		return $this;
	}
	
	//--- Protected helpers -------------------------------------------------------------------------------------------
	
	protected function determineIndent(): string
	{
		$lines = $this->lines();
		$firstLine = $lines->first();
		return substr_replace($firstLine, '', strpos($firstLine, '/**'));
	}
	
	protected function lines(): Collection
	{
		return collect(explode("\n", $this->contents));
	}
	
	protected function lineIsProperty(string $line): bool
	{
		return preg_match('/\s*\*\s*@property\s+.+/', $line);
	}
	
	protected function lineIsMethod(string $line): bool
	{
		return preg_match('/\s*\*\s*@method\s+.+/', $line);
	}
}
