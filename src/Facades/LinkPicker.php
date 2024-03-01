<?php

namespace Outerweb\FilamentLinkPicker\Facades;

use Illuminate\Support\Facades\Facade;
use Outerweb\FilamentLinkPicker\Services\LinkPicker as ServicesLinkPicker;

class LinkPicker extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ServicesLinkPicker::class;
    }
}
