<?php

namespace GrantHolle\DotenvEditor;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Config\Repository as Config;
use GrantHolle\DotenvEditor\Exceptions\FileNotFoundException;
use GrantHolle\DotenvEditor\Exceptions\NoBackupAvailableException;
use Illuminate\Support\Str;

class DotenvEditor
{
    /**
     * The formatter instance
     *
     * @var \GrantHolle\DotenvEditor\DotenvFormatter
     */
    protected $formatter;

    /**
     * The reader instance
     *
     * @var \GrantHolle\DotenvEditor\DotenvReader
     */
    protected $reader;

    /**
     * The writer instance
     *
     * @var \GrantHolle\DotenvEditor\DotenvWriter
     */
    protected $writer;

    /**
     * The file path
     *
     * @var string
     */
    public $filePath;

    /**
     * The auto backup status
     *
     * @var bool
     */
    public $autoBackup;

    /**
     * The backup path
     *
     * @var string
     */
    public $backupPath;

    /**
     * The backup filename prefix
     */
    const BACKUP_FILENAME_PREFIX = '.env.backup_';

    /**
     * The backup filename suffix
     */
    const BACKUP_FILENAME_SUFFIX = '';

    public function __construct(Container $app)
    {
        $formatterClass = config('dotenv-editor.classes.formatter');
        $this->formatter = new $formatterClass;

        $this->backupPath = Str::finish(config('dotenv-editor.backupPath'), '/');
        $this->autoBackup = config('dotenv-editor.autoBackup');

        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
            copy(__DIR__ . '/stubs/gitignore.txt', $this->backupPath . '../.gitignore');
        }

        $this->setUp($app->environmentFilePath());
    }

    /**
     * Load file for working
     *
     * @param string|null $filePath The file path
     * @param boolean $restoreIfNotFound Restore this file from other file if it's not found
     * @param string|null $restorePath The file path you want to restore from
     *
     * @return DotenvEditor
     * @throws FileNotFoundException
     * @throws NoBackupAvailableException
     */
    public function setUp($filePath = null, $restoreIfNotFound = false, $restorePath = null)
    {
        $this->filePath = $filePath ?? base_path('.env');

        $reader = config('dotenv-editor.classes.reader');
        $writer = config('dotenv-editor.classes.writer');
        $this->reader = new $reader($this->formatter, $this->filePath);
        $this->writer = new $writer($this->formatter);

        if (file_exists($this->filePath)) {
            $this->writer->setBuffer($this->getContent());
            return $this;
        }

        if ($restoreIfNotFound) {
            return $this->restore($restorePath);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Working with reading
    |--------------------------------------------------------------------------
    |
    | getContent($content)
    | getLines()
    | getKeys()
    | keyExists($key)
    | getValue($key)
    |
    */

    /**
     * Get raw content of file
     */
    public function getContent(): string
    {
        return $this->reader->content();
    }

    /**
     * Get all lines from file
     */
    public function getLines(): array
    {
        return $this->reader->lines();
    }

    public function getKeys(array $keys = []): array
    {
        $allKeys = $this->reader->keys();

        return array_filter($allKeys, function ($key) use ($keys) {
            if (!empty($keys)) {
                return in_array($key, $keys);
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);
    }

    public function keyExists(string $key): bool
    {
        return array_key_exists($key, $this->getKeys());
    }

    /**
     * Returns the key or null if it doesn't exist
     *
     * @param string $key
     * @return string|null
     */
    public function getValue(string $key)
    {
        $allKeys = $this->getKeys([$key]);

        if (array_key_exists($key, $allKeys)) {
            return $allKeys[$key]['value'];
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Working with writing
    |--------------------------------------------------------------------------
    |
    | getBuffer()
    | addEmpty()
    | addComment($comment)
    | setKeys($data)
    | setKey($key, $value = null, $comment = null, $export = false)
    | deleteKeys($keys = [])
    | deleteKey($key)
    | save()
    |
    */

    /**
     * Return content in buffer
     */
    public function getBuffer(): string
    {
        return $this->writer->getBuffer();
    }

    /**
     * Add empty line to buffer
     */
    public function addEmpty(): DotenvEditor
    {
        $this->writer->appendEmptyLine();

        return $this;
    }

    /**
     * Add comment line to buffer
     *
     * @param object
     * @return DotenvEditor
     */
    public function addComment($comment): DotenvEditor
    {
        $this->writer->appendCommentLine($comment);

        return $this;
    }

    /**
     * Set many keys to buffer
     *
     * @param  array  $data
     *
     * @return DotenvEditor
     */
    public function setKeys($data): DotenvEditor
    {
        foreach ($data as $setter) {
            if (!array_key_exists('key', $setter)) {
                continue;
            }

            $key = $this->formatter->formatKey($setter['key']);
            $value = array_key_exists('value', $setter) ? $setter['value'] : null;
            $comment = array_key_exists('comment', $setter) ? $setter['comment'] : null;
            $export = array_key_exists('export', $setter) ? $setter['export'] : false;

            if (!is_file($this->filePath) || !$this->keyExists($key)) {
                $this->writer->appendSetter($key, $value, $comment, $export);
            } else {
                $oldInfo = $this->getKeys([$key]);
                $comment = is_null($comment) ? $oldInfo[$key]['comment'] : $comment;
                $this->writer->updateSetter($key, $value, $comment, $export);
            }
        }

        return $this;
    }

    /**
     * Set one key to buffer
     *
     * @param string       $key      Key name of setter
     * @param string|null  $value    Value of setter
     * @param string|null  $comment  Comment of setter
     * @param boolean      $export   Leading key name by "export "
     *
     * @return DotenvEditor
     */
    public function setKey($key, $value = null, $comment = null, $export = false): DotenvEditor
    {
        $data = [compact('key', 'value', 'comment', 'export')];

        return $this->setKeys($data);
    }

    /**
     * Delete many keys in buffer
     *
     * @param  array $keys
     *
     * @return DotenvEditor
     */
    public function deleteKeys($keys = []): DotenvEditor
    {
        foreach ($keys as $key) {
            $this->writer->deleteSetter($key);
        }

        return $this;
    }

    /**
     * Delete on key in buffer
     *
     * @param  string  $key
     *
     * @return DotenvEditor
     */
    public function deleteKey($key): DotenvEditor
    {
        $keys = [$key];

        return $this->deleteKeys($keys);
    }

    /**
     * Save buffer to file
     */
    public function save(): DotenvEditor
    {
        if (is_file($this->filePath) && $this->autoBackup) {
            $this->backUp();
        }

        $this->writer->save($this->filePath);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Working with backups
    |--------------------------------------------------------------------------
    |
    | autoBackup($on)
    | backup()
    | getBackups()
    | getLatestBackup()
    | restore($filePath = null)
    | deleteBackups($filePaths = [])
    | deleteBackup($filePath)
    |
    */

    public function autoBackup(bool $backUp = true): DotenvEditor
    {
        $this->autoBackup = $backUp;

        return $this;
    }

    public function backUp(): DotenvEditor
    {
        copy(
            $this->filePath,
            $this->backupPath . self::BACKUP_FILENAME_PREFIX . date('Y_m_d_His') . self::BACKUP_FILENAME_SUFFIX
        );

        return $this;
    }

    public function getBackups(): array
    {
        $backups = array_diff(scandir($this->backupPath), array('..', '.'));
        $output = [];

        foreach ($backups as $backup) {
            $filenamePrefix = preg_quote(self::BACKUP_FILENAME_PREFIX, '/');
            $filenameSuffix = preg_quote(self::BACKUP_FILENAME_SUFFIX, '/');
            $filenameRegex  = '/^' .$filenamePrefix. '(\d{4})_(\d{2})_(\d{2})_(\d{2})(\d{2})(\d{2})' .$filenameSuffix. '$/';

            $datetime = preg_replace($filenameRegex, '$1-$2-$3 $4:$5:$6', $backup);

            $data = [
                'filename'   => $backup,
                'filepath'   => $this->backupPath . $backup,
                'created_at' => $datetime,
            ];

            $output[] = $data;
        }

        return $output;
    }

    public function getLatestBackup(): array
    {
        $backups = $this->getBackups();

        if (empty($backups)) {
            return null;
        }

        $latestBackup = 0;
        foreach ($backups as $backup) {
            $timestamp = strtotime($backup['created_at']);
            if ($timestamp > $latestBackup) {
                $latestBackup = $timestamp;
            }
        }

        $fileName  = self::BACKUP_FILENAME_PREFIX . date("Y_m_d_His", $latestBackup) . self::BACKUP_FILENAME_SUFFIX;
        $filePath  = $this->backupPath . $fileName;
        $createdAt = date("Y-m-d H:i:s", $latestBackup);

        return [
            'filename'   => $fileName,
            'filepath'   => $filePath,
            'created_at' => $createdAt
        ];
    }

    public function restore(string $filePath = null): DotenvEditor
    {
        if (is_null($filePath)) {
            $latestBackup = $this->getLatestBackup();

            if (is_null($latestBackup)) {
                throw new NoBackupAvailableException("There are no available backups!");
            }

            $filePath = $latestBackup['filepath'];
        }

        if (!is_file($filePath)) {
            throw new FileNotFoundException("File does not exist at path {$filePath}");
        }

        copy($filePath, $this->filePath);
        $this->writer->setBuffer($this->getContent());

        return $this;
    }

    public function deleteBackups(array $filePaths = []): DotenvEditor
    {
        if (empty($filePaths)) {
            $filePaths = collect($this->getBackups())->map(function ($backup) {
                return $backup['filepath'];
            });
        }

        collect($filePaths)->each(function ($filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        });

        return $this;
    }

    public function deleteBackup(string $filePath = null): DotenvEditor
    {
        return $this->deleteBackups([$filePath]);
    }
}
