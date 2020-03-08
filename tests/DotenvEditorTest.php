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
}