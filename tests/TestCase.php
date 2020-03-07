<?php

namespace GrantHolle\Tests;

use GrantHolle\DotenvEditor\DotenvEditor;
use GrantHolle\DotenvEditor\DotenvEditorServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->editor = $this->app->make(DotenvEditor::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            DotenvEditorServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'DotenvEditor' => \GrantHolle\DotenvEditor\Facades\DotenvEditor::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__);
    }
}