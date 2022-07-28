<?php


namespace Lukaswhite\DocumentConverter;

use Lukaswhite\DocumentConverter\Contracts\ExecutesShellCommands;

/**
 * Class Executor
 *
 * Very simply, this class runs shell commands and optionally returns the output.
 *
 * It can be swapped out for something more sophisticated, or mocked for testing.
 *
 * @package Lukaswhite\DocumentConverter
 */
class Executor implements ExecutesShellCommands
{
    /**
     * @param string $command
     * @param bool $output
     * @return string|null
     * @codeCoverageIgnore
     */
    public function run(string $command, bool $output = false): ?string
    {
        return $output ? shell_exec(sprintf('%s 2>&1', $command)) : shell_exec($command);
    }
}