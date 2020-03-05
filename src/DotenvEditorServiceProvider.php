<?php

namespace GrantHolle\DotenvEditor;

use GrantHolle\DotenvEditor\Console\Commands\DotenvBackupCommand;
use GrantHolle\DotenvEditor\Console\Commands\DotenvDeleteKeyCommand;
use GrantHolle\DotenvEditor\Console\Commands\DotenvGetBackupsCommand;
use GrantHolle\DotenvEditor\Console\Commands\DotenvGetKeysCommand;
use GrantHolle\DotenvEditor\Console\Commands\DotenvRestoreCommand;
use GrantHolle\DotenvEditor\Console\Commands\DotenvSetKeyCommand;
use Illuminate\Support\ServiceProvider;

class DotenvEditorServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('dotenv-editor.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(DotenvEditor::class, DotenvEditor::class);

        $this->registerCommands();

        $this->mergeConfigFrom(__DIR__ . '/config.php', 'dotenv-editor');
    }

    /**
     * Register commands
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands(DotenvBackupCommand::class);
        $this->commands(DotenvDeleteKeyCommand::class);
        $this->commands(DotenvGetBackupsCommand::class);
        $this->commands(DotenvGetKeysCommand::class);
        $this->commands(DotenvRestoreCommand::class);
        $this->commands(DotenvSetKeyCommand::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            DotenvEditor::class,
        ];
    }
}
