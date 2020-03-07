<?php

namespace GrantHolle\DotenvEditor\Facades;

use Illuminate\Support\Facades\Facade;

class DotenvEditor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GrantHolle\DotenvEditor\DotenvEditor::class;
    }
}
