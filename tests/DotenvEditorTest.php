<?php


namespace GrantHolle\Tests;

use GrantHolle\DotenvEditor\DotenvEditor;

class DotenvEditorTest extends TestCase
{
    public function test_env_file_path_gets_set_correctly()
    {
        $this->assertEquals(__DIR__ . '/.env', $this->editor->filePath);

        $this->editor->setUp(null);
        $this->assertEquals(base_path('.env'), $this->editor->filePath);
    }

    public function test_can_retrieve_key_value()
    {
        $value = $this->editor->get('APP_NAME');
        $this->assertEquals('Laravel', $value);
    }

    public function test_can_get_keys()
    {
        $this->editor->setUp(__DIR__ . '/.env2');

        $allKeys = $this->editor->getKeys();
        $this->assertEquals(['APP_KEY', 'CACHE_DRIVER', 'MAIL_PORT'], array_keys($allKeys));

        $keys = $this->editor->getKeys(['APP_KEY', 'CACHE_DRIVER']);
        $this->assertEquals(['APP_KEY', 'CACHE_DRIVER'], array_keys($keys));

        $key = $this->editor->getKey('MAIL_PORT');
        $this->assertEquals(['line', 'export', 'value', 'comment'], array_keys($key));
    }
}