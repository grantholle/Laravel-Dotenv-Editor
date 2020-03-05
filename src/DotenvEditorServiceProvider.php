<?php

namespace GrantHolle\DotenvEditor;

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
        $this->app->bind('dotenv-editor', 'GrantHolle\DotenvEditor\DotenvEditor');

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
        $this->app->bind('command.dotenv.backup', 'GrantHolle\DotenvEditor\Console\Commands\DotenvBackupCommand');
        $this->app->bind('command.dotenv.deletekey', 'GrantHolle\DotenvEditor\Console\Commands\DotenvDeleteKeyCommand');
        $this->app->bind('command.dotenv.getbackups', 'GrantHolle\DotenvEditor\Console\Commands\DotenvGetBackupsCommand');
        $this->app->bind('command.dotenv.getkeys', 'GrantHolle\DotenvEditor\Console\Commands\DotenvGetKeysCommand');
        $this->app->bind('command.dotenv.restore', 'GrantHolle\DotenvEditor\Console\Commands\DotenvRestoreCommand');
        $this->app->bind('command.dotenv.setkey', 'GrantHolle\DotenvEditor\Console\Commands\DotenvSetKeyCommand');

        $this->commands('command.dotenv.backup');
        $this->commands('command.dotenv.deletekey');
        $this->commands('command.dotenv.getbackups');
        $this->commands('command.dotenv.getkeys');
        $this->commands('command.dotenv.restore');
        $this->commands('command.dotenv.setkey');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'dotenv-editor',
            'command.dotenv.backup',
            'command.dotenv.deletekey',
            'command.dotenv.getbackups',
            'command.dotenv.getkeys',
            'command.dotenv.restore',
            'command.dotenv.setkey'
        ];
    }
}
