<?php namespace GrantHolle\DotenvEditor\Console\Traits;

use GrantHolle\DotenvEditor\DotenvEditor;

trait CreateCommandInstanceTrait
{
    /**
     * The .env file editor instance
     *
     * @var \GrantHolle\DotenvEditor\DotenvEditor
     */
    protected $editor;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DotenvEditor $editor)
    {
        parent::__construct();

        $this->editor = $editor;
    }

    /**
     * Execute the console command.
     * This is alias of the method fire()
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->fire();
    }
}
