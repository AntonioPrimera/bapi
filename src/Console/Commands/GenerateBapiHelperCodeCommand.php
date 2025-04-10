<?php
namespace AntonioPrimera\Bapi\Console\Commands;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\FileSystem\OS;
use AntonioPrimera\Bapi\Console\Commands\Support\BapiClassDescription;
use AntonioPrimera\Bapi\Console\Commands\Support\DocBlock;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateBapiHelperCodeCommand extends Command
{
	protected $signature = 'bapi:ide {file?} {--G|generate-doc-blocks}';
	protected $description = "Generate IDE helper code for all BAPI classes and optionally generate doc blocks for a specific BAPI class.\n"
	. "If --generate-doc-blocks is set, doc blocks will be generated for all BAPI classes.";
	
	public function handle(): void
	{
		//generate doc blocks for a specific BAPI class and regenerate the helper code for all BAPI classes
		if ($this->argument('file'))
			$this->generateDocBlockForBapi($this->argument('file'));
		
		//generate doc blocks for all BAPI classes and optionally regenerate the helper code for all BAPI classes
		if ($this->option('generate-doc-blocks'))
			$this->generateDocBlocksForAllBapis();
		
		$this->generateHelperCodeForAllBapis();
	}
	
	//--- DocBlock generation methods ---------------------------------------------------------------------------------
	
	/**
	 * Generate doc blocks for all BAPI classes in the project
	 */
	protected function generateDocBlocksForAllBapis(): void
	{
		$this->bapiList()
			->each(fn(BapiClassDescription $bapiDescription) => $this->generateDocBlock($bapiDescription, true));
	}
	
	/**
	 * Generate a doc block for a specific BAPI class
	 */
	protected function generateDocBlockForBapi(string $path, bool $verbose = false): void
	{
		$absolutePath = OS::isAbsolutePath($path) ? $path : base_path($path);
		$this->info("Generating doc block for BAPI class: $path");
		$file = File::instance($absolutePath);
		
		//if the file doesn't exist, just return
		if (!$file->exists) {
			if ($verbose)
				$this->info("The specified file does not exist: $path");
			
			return;
		}
		
		$bapiDescription = BapiClassDescription::fromFile($file);
		if (!$bapiDescription->isBapiClass && $verbose)
			$this->info("The specified file is not a BAPI class: $path");
		
		if ($bapiDescription->isBapiClass)
			$this->generateDocBlock($bapiDescription);
	}
	
	//--- Helper code generation methods ------------------------------------------------------------------------------
	
	/**
	 * Find all bapi classes in the app and generate an IDE helper file for them.
	 */
	protected function generateHelperCodeForAllBapis(): void
	{
		$targetFile = $this->prepareFolder(base_path('vendor/_bapis'))
			->file('_ide_helper_bapis.php');
		
		$bapiList = $this->bapiList();
		$targetFile->putContents($this->generateFile($bapiList));
		
		$this->info('BAPI IDE helper file generated successfully for ' . $bapiList->count() . ' BAPI classes.');
	}
	
	/**
	 * Prepare the target folder for the IDE helper code file.
	 */
	protected function prepareFolder(string $targetFolderPath): Folder
	{
		$targetFolder = Folder::instance($targetFolderPath);
		
		if (!$targetFolder->exists())
			$targetFolder->create();
		
		if (!$targetFolder->hasFile('.gitignore'))
			$targetFolder->file('.gitignore')->putContents('*');
		
		return $targetFolder;
	}
	
	/**
	 * Return a collection of BapiClassDescription objects,
	 * one for each BAPI class in the app.
	 */
	protected function bapiList(): Collection
	{
		return collect(Folder::instance(app_path('Bapis'))->getAllFiles())
			->map(fn(File $file) => BapiClassDescription::fromFile($file))
			->filter(fn(BapiClassDescription $bapi) => $bapi->isBapiClass);
	}
	
	//--- Write helpers -----------------------------------------------------------------------------------------------
	
	/**
	 * Generate the IDE helper code file content.
	 */
	protected function generateFile(Collection $bapiDescriptions): string
	{
		$helperCode = $bapiDescriptions
			->groupBy('namespace')
			->map(fn(Collection $bapiDescriptions, string $namespace) => $this->namespaceCode($namespace, $bapiDescriptions))
			->implode("\n\n");
		
		return "<?php\n\n$helperCode";
	}
	
	/**
	 * Generate the code for a namespace (with use statements and all classes in that namespace).
	 */
	protected function namespaceCode(string $namespace, Collection $bapiDescriptions): string
	{
		$includes = $this->generateIncludes($bapiDescriptions);
		$classesCode = $bapiDescriptions
			->map(fn(BapiClassDescription $bapiDescription) => $bapiDescription->helperCode())
			->implode("\n");
		
		return "namespace $namespace {\n\n$includes\n\n$classesCode\n}";
	}
	
	/**
	 * Generate the use statements for all classes in the given collection (usually a namespace).
	 * todo: handle the case when several classes with the same name are used in a namespace
	 *      (e.g. Support\Collection and Eloquent\Collection)
	 */
	protected function generateIncludes(Collection $bapiDescriptions): string
	{
		return $bapiDescriptions
			->flatMap(fn(BapiClassDescription $bapiDescription) => $bapiDescription->includes)
			->unique()
			->map(fn(string $include, string $fqn) => "    use $fqn;")
			->implode("\n");
	}
	
	protected function generateDocBlock(BapiClassDescription $bapiDescription, bool $verbose = false): void
	{
		$properties = $bapiDescription->handleMethodParameters;
		
		//generate the docBlock
		$docBlock = DocBlock::fromClass($bapiDescription->fqn);
		$originalDocBlock = $docBlock->contents;
		$filePosition = $docBlock->filePosition($bapiDescription->file);
		
		//clear the existing properties and add the new ones
		$docBlock->clearProperties();
		$properties->each(fn(string $property) => $docBlock->addPropertySignature($property));
		
		//replace the doc block in the file if it has changed
		if ($docBlock->contents !== $originalDocBlock) {
			$docBlock->replaceInFile($filePosition);
			
			if ($verbose)
				$this->info("Doc block generated successfully for BAPI: $bapiDescription->fqn");
		}
	}
}
