<?php namespace GrantHolle\DotenvEditor\Contracts;

interface DotenvFormatter
{
    /**
     * Formats the key of a setter for writing
     *
     * @param string $key
     * @return string
     */
    public function formatKey(string $key): string;

    /**
     * Formats the value of a setter for writing
     *
     * @param string $value
     * @param bool $forceQuotes
     * @return string
     */
    public function formatValue(string $value, bool $forceQuotes = false): string;

    /**
     * Formats a comment for writing
     *
     * @param string $comment
     * @return string
     */
    public function formatComment(string $comment): string;

    /**
     * Builds a setter line from the individual components for writing
     *
     * @param string $key
     * @param string|null $value
     * @param string|null $comment
     * @param bool $export
     * @return string
     */
    public function formatSetterLine(string $key, string $value = null, string $comment = null, bool $export = false): string;

    /**
     * Normalising the key of setter to reading
     *
     * @param string $key
     * @return string
     */
    public function normaliseKey(string $key): string;

    /**
     * Normalising the value of setter to reading
     *
     * @param string $value
     * @param string $quote
     * @return string
     */
    public function normaliseValue(string $value, string $quote = ''): string;

    /**
     * Normalising the comment to reading
     *
     * @param string $comment
     * @return string
     */
    public function normaliseComment(string $comment): string;

    /**
     * Parse a line into an array of type, export, key, value and comment
     *
     * @param string $line
     * @return string
     */
    public function parseLine(string $line): array;
}
