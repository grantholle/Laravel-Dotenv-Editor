<?php

namespace GrantHolle\DotenvEditor;

use GrantHolle\DotenvEditor\Contracts\DotenvFormatter as DotenvFormatterContract;
use GrantHolle\DotenvEditor\Exceptions\InvalidValueException;

class DotenvFormatter implements DotenvFormatterContract
{
    public function formatKey($key): string
    {
        return trim(str_replace(['export ', '\'', '"', ' '], '', $key));
    }

    public function formatValue(string $value, $forceQuotes = false): string
    {
        if (!$forceQuotes && !preg_match('/[#\s"\'\\\\]|\\\\n/', $value)) {
            return $value;
        }

        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\"', $value);
        $value = "\"{$value}\"";

        return $value;
    }

    public function formatComment($comment): string
    {
        $comment = trim($comment, '# ');

        return (strlen($comment) > 0) ? " # {$comment}" : "";
    }

    public function formatSetterLine(string $key, string $value = null, string $comment = null, bool $export = false): string
    {
        $forceQuotes = (strlen($comment) > 0 && strlen(trim($value)) == 0);
        $value = $this->formatValue($value, $forceQuotes);
        $key = $this->formatKey($key);
        $comment = $this->formatComment($comment);
        $export = $export ? 'export ' : '';

        return "{$export}{$key}={$value}{$comment}";
    }

    public function normaliseKey($key): string
    {
        return $this->formatKey($key);
    }

    public function normaliseValue(string $value, string $quote = ''): string
    {
        if (strlen($quote) == 0) {
            return trim($value);
        }

        $value = str_replace("\\$quote", $quote, $value);

        return str_replace('\\\\', '\\', $value);
    }

    public function normaliseComment(string $comment): string
    {
        return trim($comment, '# ');
    }

    public function parseLine(string $line): array
    {
        $output = [
            'type' => 'unknown',
            'export' => null,
            'key' => null,
            'value' => null,
            'comment' => null,
        ];

        if ($this->isEmpty($line)) {
            $output['type'] = 'empty';

            return $output;
        }

        if ($this->isComment($line)) {
            $output['type'] = 'comment';
            $output['comment'] = $this->normaliseComment($line);

            return $output;
        }

        if (!$this->looksLikeSetter($line)) {
            return $output;
        }

        list($key, $data) = array_map('trim', explode('=', $line, 2));
        $export = $this->isExportKey($key);
        $key = $this->normaliseKey($key);

        if (!$data && $data !== '0') {
            $value = '';
            $comment = '';
        } else {
            if ($this->beginsWithAQuote($data)) { // data starts with a quote
                $quote = $data[0];
                $regexPattern = sprintf(
                    '/^
                    %1$s          # match a quote at the start of the data
                    (             # capturing sub-pattern used
                     (?:          # we do not need to capture this
                      [^%1$s\\\\] # any character other than a quote or backslash
                      |\\\\\\\\   # or two backslashes together
                      |\\\\%1$s   # or an escaped quote e.g \"
                     )*           # as many characters that match the previous rules
                    )             # end of the capturing sub-pattern
                    %1$s          # and the closing quote
                    (.*)$         # and discard any string after the closing quote
                    /mx',
                    $quote
                );

                $value = preg_replace($regexPattern, '$1', $data);
                $extant = preg_replace($regexPattern, '$2', $data);

                $value = $this->normaliseValue($value, $quote);
                $comment = ($this->isComment($extant))
                    ? $this->normaliseComment($extant)
                    : '';
            } else {
                $parts = explode(' #', $data, 2);

                $value = $this->normaliseValue($parts[0]);
                $comment = (isset($parts[1]))
                    ? $this->normaliseComment($parts[1])
                    : '';

                // Unquoted values cannot contain whitespace
                if (preg_match('/\s+/', $value) > 0) {
                    throw new InvalidValueException('Dotenv values containing spaces must be surrounded by quotes.');
                }
            }
        }

        $output['type'] = 'setter';
        $output['export'] = $export;
        $output['key'] = $key;
        $output['value'] = $value;
        $output['comment'] = $comment;

        return $output;
    }

    protected function isEmpty(string $line): bool
    {
        return strlen(trim($line)) == 0;
    }

    protected function isComment(string $line): bool
    {
        return strpos(ltrim($line), '#') === 0;
    }

    protected function looksLikeSetter(string $line): bool
    {
        return strpos($line, '=') !== false && strpos($line, '=') !== 0;
    }

    protected function isExportKey(string $key): bool
    {
        return preg_match('/^export\h.*$/', trim($key));
    }

    protected function beginsWithAQuote(string $data): bool
    {
        return strpbrk($data[0], '"\'') !== false;
    }
}
