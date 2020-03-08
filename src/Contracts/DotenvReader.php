<?php

namespace GrantHolle\DotenvEditor\Contracts;

interface DotenvReader
{
    public function __construct(DotenvFormatter $formatter, string $filePath);

    /**
     * Gets the content of the environment file
     */
    public function content(): string;

    /**
     * Gets all the information about the lines of the environment file
     */
    public function lines(): array;

    /**
     * Get all the information about the environment keys
     */
    public function keys(): array;
}
