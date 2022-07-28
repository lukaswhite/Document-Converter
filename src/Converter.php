<?php


namespace Lukaswhite\DocumentConverter;


use Lukaswhite\DocumentConverter\Contracts\ExecutesShellCommands;
use Lukaswhite\DocumentConverter\Exceptions\DirectoryDoesNotExistException;
use Lukaswhite\DocumentConverter\Exceptions\FileNotFoundException;
use Lukaswhite\DocumentConverter\Exceptions\FileNotGeneratedException;
use Lukaswhite\DocumentConverter\Exceptions\LibreofficeNotInstalledException;
use Lukaswhite\DocumentConverter\Exceptions\NotDirectoryException;

/**
 * Class Converter
 * @package Lukaswhite\DocumentConverter
 */
class Converter
{
    /**
     * @var string
     */
    protected $bin = 'libreoffice';

    /**
     * @var Executor
     */
    protected $executor;

    /**
     * @var string
     */
    protected $filepath;

    /**
     * @var string
     */
    protected $outputDirectory;

    /**
     * @var string
     */
    protected $outputFileame;

    /**
     * @var bool
     */
    protected $removeTemp = true;

    /**
     * Converter constructor.
     * @param string $filepath
     * @throws FileNotFoundException
     */
    public function __construct(string $filepath)
    {
        if(!file_exists($filepath)){
            throw new FileNotFoundException('File not found');
        }
        $this->filepath = $filepath;
        $this->outputDirectory = pathinfo($this->filepath, PATHINFO_DIRNAME);
        $this->executor = new Executor();
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return trim($this->executeCommand(sprintf('%s --version 2>&1', $this->bin), true));
    }

    /**
     * Specify the output filename. If this isn't called, the resulting file will just
     * have the same name, but a different extension.
     *
     * @param string $filename
     * @return $this
     */
    public function outputAs(string $filename): self
    {
        $this->outputFileame = pathinfo($filename, PATHINFO_FILENAME);
        return $this;
    }

    /**
     * Convert the file to the format represented by the specified extension.
     *
     * @param string $extension
     * @return Result
     * @throws FileNotGeneratedException
     */
    public function toFormat(string $extension): Result
    {
        return $this->doConversion($extension);
    }

    /**
     * Convert the file to a PDF.
     *
     * @return Result
     * @throws FileNotGeneratedException
     */
    public function toPDF(): Result
    {
        return $this->doConversion('pdf');
    }

    /**
     * @return $this
     */
    public function keepTemporaryFile(): self
    {
        $this->removeTemp = false;
        return $this;
    }

    /**
     * @param string $extension
     * @return Result
     * @throws FileNotGeneratedException
     */
    protected function doConversion(string $extension): Result
    {
        if(!$this->executableExists()){
            throw new LibreofficeNotInstalledException('libreoffice not found. Have you installed it?');
        }

        $start = microtime(true);

        // We're expecting a file with the same name, but the required extension
        $expected = sprintf('%s.%s', pathinfo($this->filepath, PATHINFO_FILENAME), $extension);
        $expectedFilepath = sprintf('%s%s%s', $this->outputDirectory, DIRECTORY_SEPARATOR, $expected);

        $ex = $this->executeCommand($this->generateCommand($extension));

        if(!file_exists($expectedFilepath)){
            throw new FileNotGeneratedException('The file has not been generated');
        }

        // If the user hasn't specified a specific filename, or wants to output it elsewhere,
        // then we're done.
        if(empty($this->outputFileame) && $this->outputtingToSameDirectory()){
            return (Result::success())
                ->setTimes($start, microtime(true))
                ->setFilepath($expectedFilepath);
        }

        // Generate the desired filename and filepath
        $filename = sprintf('%s.%s', $this->outputFileame, $extension);
        $filepath = sprintf('%s%s%s', $this->outputDirectory, DIRECTORY_SEPARATOR, $filename);

        // Copy the file to the required location, then optionally delete it
        copy($expectedFilepath, $filepath);
        if($this->removeTemp){
            // @codeCoverageIgnoreStart
            unlink($expectedFilepath);
            // @codeCoverageIgnoreEnd
        }

        return (Result::success())
            ->setTimes($start, microtime(true))
            ->setFilepath($filepath);
    }

    /**
     * @param string $command
     * @param bool $output
     * @return string|null
     */
    public function executeCommand(string $command, bool $output = false)
    {
        return $this->executor->run($command, $output);
    }

    /**
     * Helper method to check whether the file is to be generated in the same directory
     * as it's already in.
     *
     * @return bool
     */
    public function outputtingToSameDirectory(): bool
    {
        return pathinfo($this->filepath, PATHINFO_DIRNAME) ===
            $this->outputDirectory;
    }

    /**
     * @param string $bin
     * @return self
     */
    public function setExecutable(string $bin): self
    {
        $this->bin = $bin;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    /**
     * @param string $directory
     * @return self
     */
    public function outputTo(string $directory): self
    {
        if(!file_exists($directory)){
            throw new DirectoryDoesNotExistException('The output directory does not exist');
        }
        if(!is_dir($directory)){
            throw new NotDirectoryException('You can only output to a directory');
        }
        $this->outputDirectory = $directory;
        return $this;
    }

    /**
     * @param ExecutesShellCommands $executor
     * @return $this
     */
    public function executeWith(ExecutesShellCommands $executor)
    {
        $this->executor = $executor;
        return $this;
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function generateCommand(string $extension)
    {
        $oriFile = escapeshellarg($this->filepath);
        $outputDirectory = escapeshellarg($this->outputDirectory);

        return "{$this->bin} --headless --convert-to {$extension} {$oriFile} --outdir {$outputDirectory}";
    }

    /**
     * Check whether the Libreoffice executable exists.
     * @return bool
     */
    public function executableExists()
    {
        return (null === $this->executor->run(sprintf('command -v %s', $this->bin))) ? false : true;
    }
}