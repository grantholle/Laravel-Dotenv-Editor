<?php

namespace GrantHolle\DotenvEditor;

use GrantHolle\DotenvEditor\Contracts\DotenvFormatter as DotenvFormatterContract;
use GrantHolle\DotenvEditor\Contracts\DotenvReader as DotenvReaderContract;
use GrantHolle\DotenvEditor\Exceptions\UnableReadFileException;

/**
 * The DotenvReader class.
 *
 * @package GrantHolle\DotenvEditor
 * @author Jackie Do <anhvudo@gmail.com>
 */
class DotenvReader implements DotenvReaderContract
{
    /**
     * The file path
     *
     * @var string
     */
    protected $filePath;

    /**
     * Instance of GrantHolle\DotenvEditor\DotenvFormatter
     *
     * @var object
     */
    protected $formatter;

    /**
     * Create a new reader instance
     *
     * @param \GrantHolle\DotenvEditor\Contracts\DotenvFormatter $formatter
     * @param string $filePath
     */
    public function __construct(DotenvFormatterContract $formatter, string $filePath)
    {
        $this->formatter = $formatter;
        $this->filePath = $filePath;
    }

    public function content(): string
    {
        return file_get_contents($this->filePath);
    }

    public function lines(): array
    {
        $content = [];
        $lines = $this->readLinesFromFile();

        foreach ($lines as $row => $line) {
            $data = [
                'line' => $row + 1,
                'raw_data' => $line,
                'parsed_data' => $this->formatter->parseLine($line)
            ];

            $content[] = $data;
        }

        return $content;
    }

    public function keys(): array
    {
        $content = [];
        $lines   = $this->readLinesFromFile();

        foreach ($lines as $row => $line) {
            $data = $this->formatter->parseLine($line);

            if ($data['type'] === 'setter') {
                $content[$data['key']] = [
                    'line' => $row + 1,
                    'export' => $data['export'],
                    'value' => $data['value'],
                    'comment' => $data['comment']
                ];
            }
        }

        return $content;
    }

    protected function readLinesFromFile(): array
    {
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($this->filePath, FILE_IGNORE_NEW_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        return $lines;
    }
}
