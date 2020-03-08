<?php

namespace GrantHolle\DotenvEditor;

use GrantHolle\DotenvEditor\Contracts\DotenvFormatter as DotenvFormatterContract;
use GrantHolle\DotenvEditor\Contracts\DotenvWriter as DotenvWriterContract;
use GrantHolle\DotenvEditor\Exceptions\UnableWriteToFileException;

/**
 * The DotenvWriter writer.
 *
 * @package GrantHolle\DotenvEditor
 * @author Jackie Do <anhvudo@gmail.com>
 */
class DotenvWriter implements DotenvWriterContract
{
    /**
     * The path to the file to write
     *
     * @var string
     */
    protected $filePath;

    /**
     * The content buffer
     *
     * @var string
     */
    protected $buffer;

    /**
     * The formatter instance
     *
     * @var \GrantHolle\DotenvEditor\DotenvFormatter
     */
    protected $formatter;

    /**
     * Create a new writer instance
     *
     * @param DotenvFormatterContract $formatter
     */
    public function __construct(DotenvFormatterContract $formatter)
    {
        $this->formatter = $formatter;
    }

    public function setBuffer(string $content): DotenvWriterContract
    {
        $this->buffer = $content;

        return $this;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    protected function appendLine(string $text = null): DotenvWriterContract
    {
        $this->buffer .= $text . PHP_EOL;

        return $this;
    }

    public function appendEmptyLine(): DotenvWriterContract
    {
        return $this->appendLine();
    }

    public function appendCommentLine(string $comment): DotenvWriterContract
    {
        return $this->appendLine("# {$comment}");
    }

    public function appendSetter(string $key, string $value = null, string $comment = null, bool $export = false): DotenvWriterContract
    {
        $line = $this->formatter->formatSetterLine($key, $value, $comment, $export);

        return $this->appendLine($line);
    }

    public function updateSetter(string $key, string $value = null, string $comment = null, bool $export = false): DotenvWriterContract
    {
        $pattern = "/^(export\h)?\h*{$key}=.*/m";
        $line = $this->formatter->formatSetterLine($key, $value, $comment, $export);
        $this->buffer = preg_replace($pattern, $line, $this->buffer);

        return $this;
    }

    public function deleteSetter(string $key): DotenvWriterContract
    {
        $pattern = "/^(export\h)?\h*{$key}=.*\n/m";
        $this->buffer = preg_replace($pattern, null, $this->buffer);

        return $this;
    }

    public function save(string $filePath): DotenvWriterContract
    {
        file_put_contents($filePath, $this->buffer);

        return $this;
    }
}
