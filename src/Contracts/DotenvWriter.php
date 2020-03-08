<?php namespace GrantHolle\DotenvEditor\Contracts;

interface DotenvWriter
{
    public function __construct(DotenvFormatter $formatter);

    /**
     * Load current content into buffer
     *
     * @param string $content
     * @return DotenvWriter
     */
    public function setBuffer(string $content): DotenvWriter;

    /**
     * Return content in buffer
     */
    public function getBuffer(): string;

    /**
     * Append empty line to buffer
     */
    public function appendEmptyLine(): DotenvWriter;

    /**
     * Append comment line to buffer
     *
     * @param string $comment
     * @return DotenvWriter
     */
    public function appendCommentLine(string $comment): DotenvWriter;

    /**
     * Append one setter to buffer
     *
     * @param string $key
     * @param string|null $value
     * @param string|null $comment
     * @param boolean $export
     * @return DotenvWriter
     */
    public function appendSetter(string $key, string $value = null, string $comment = null, bool $export = false): DotenvWriter;

    /**
     * Update one setter in buffer
     *
     * @param string $key
     * @param string|null $value
     * @param string|null $comment
     * @param boolean $export
     * @return DotenvWriter
     */
    public function updateSetter(string $key, string $value = null, string $comment = null, bool $export = false): DotenvWriter;

    /**
     * Delete one setter in buffer
     *
     * @param string $key
     * @return DotenvWriter
     */
    public function deleteSetter(string $key): DotenvWriter;

    /**
     * Save buffer to special file path
     *
     * @param string $filePath
     * @return DotenvWriter
     */
    public function save(string $filePath): DotenvWriter;
}
