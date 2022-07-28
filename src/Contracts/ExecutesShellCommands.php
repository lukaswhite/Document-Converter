<?php


namespace Lukaswhite\DocumentConverter\Contracts;


interface ExecutesShellCommands
{
    /**
     * @param string $command
     * @param bool $output
     * @return string|null
     * @codeCoverageIgnore
     */
    public function run(string $command, bool $output = false): ?string;
}