<?php


namespace Lukaswhite\DocumentConverter\Tests;


use Lukaswhite\DocumentConverter\Exceptions\DirectoryDoesNotExistException;
use Lukaswhite\DocumentConverter\Exceptions\FileNotFoundException;
use Lukaswhite\DocumentConverter\Exceptions\FileNotGeneratedException;
use Lukaswhite\DocumentConverter\Exceptions\LibreofficeNotInstalledException;
use Lukaswhite\DocumentConverter\Exceptions\NotDirectoryException;
use Lukaswhite\DocumentConverter\Executor;
use PHPUnit\Framework\TestCase;
use Lukaswhite\DocumentConverter\Converter;

class ConverterTest extends TestCase
{
    public function test_can_create_instance()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);
        $this->assertInstanceOf(Converter::class, $converter);
    }

    public function test_output_path_defaults_to_same_directory()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);
        $this->assertEquals('./tests/fixtures', $converter->getOutputDirectory());
        $this->assertTrue($converter->outputtingToSameDirectory());
    }

    public function test_can_set_output_directory()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);
        $converter->outputTo('./tests/fixtures/out');
        $this->assertEquals('./tests/fixtures/out', $converter->getOutputDirectory());
        $this->assertFalse($converter->outputtingToSameDirectory());
    }

    public function test_output_directory_must_exist()
    {
        $this->expectException(DirectoryDoesNotExistException::class);
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);
        $converter->outputTo('./tests/fixtures/does-not-exist');
    }

    public function test_output_directory_must_be_a_directory()
    {
        $this->expectException(NotDirectoryException::class);
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);
        $converter->outputTo('./tests/fixtures/document.odt');
    }

    public function test_ensures_file_exists()
    {
        $this->expectException(FileNotFoundException::class);
        $filepath = './tests/fixtures/does-not-exist.doc';
        $converter = new Converter($filepath);
    }

    public function test_can_get_version()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);

        $stub = $this->getMockBuilder(Executor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $stub->method('run')
            ->withAnyParameters()
            ->willReturn('  Version One ');

        $converter->executeWith($stub);

        $this->assertEquals('Version One', $converter->getVersion());
    }

    public function test_can_convert_to_pdf()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);

        $stub = $this->getMockBuilder(Executor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $stub->method('run')
            ->withAnyParameters()
            ->willReturn('');

        $converter->executeWith($stub);

        $result = $converter->toPDF();
        $this->assertTrue($result->isOk());
        $this->assertEquals('document.pdf', $result->getFilename());
        $this->assertEquals('./tests/fixtures/document.pdf', $result->getFilepath());

        $this->assertEquals('pdf', $result->getExtension());
        $this->assertEquals(9814, $result->getFilesize());
        $this->assertTrue(is_float($result->getTimeElapsed()));

    }

    public function test_can_specify_output_filename()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);

        $stub = $this->getMockBuilder(Executor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $stub->method('run')
            ->withAnyParameters()
            ->willReturn('');

        $converter->executeWith($stub);

        $result = $converter
            ->outputAs('output')
            ->keepTemporaryFile()
            ->toPDF();

        $this->assertTrue($result->isOk());
        $this->assertEquals('output.pdf', $result->getFilename());
        $this->assertEquals('./tests/fixtures/output.pdf', $result->getFilepath());

    }

    public function test_checks_file_has_been_created()
    {
        $filepath = './tests/fixtures/document2.doc';
        $converter = new Converter($filepath);

        $stub = $this->getMockBuilder(Executor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $stub->method('run')
            ->withAnyParameters()
            ->willReturn('');

        $converter->executeWith($stub);

        $this->expectException(FileNotGeneratedException::class);
        $result = $converter
            ->toPDF();


    }

    public function test_can_convert_to_other_formats()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);

        $stub = $this->getMockBuilder(Executor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $stub->method('run')
            ->withAnyParameters()
            ->willReturn('');

        $converter->executeWith($stub);

        $result = $converter->toFormat('odt');
        $this->assertTrue($result->isOk());
        $this->assertEquals('document.odt', $result->getFilename());
        $this->assertEquals('./tests/fixtures/document.odt', $result->getFilepath());

    }

    public function test_can_set_executable()
    {
        $filepath = './tests/fixtures/document.doc';
        $converter = new Converter($filepath);

        $stub = $this->getMockBuilder(Executor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['run'])
            ->getMock();

        $stub->method('run')
            ->withAnyParameters()
            ->willReturn('Version 2');

        $stub->expects($this->any())->method('run')->with('suibdiusbifudbsuf --version', true);

        //$this->assertFalse($converter->executableExists());
        $this->expectException(LibreofficeNotInstalledException::class);
        $converter->setExecutable('suibdiusbifudbsuf')->toPDF();

        //$this->assertEquals('Version 2', $converter->setExcutable('lib')->getVersion());
    }

}