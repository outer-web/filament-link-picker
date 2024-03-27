<?php

namespace Outerweb\FilamentLinkPicker\Filament\Plugins;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Outerweb\FilamentLinkPicker\Facades\LinkPicker;

class FilamentLinkPickerPlugin implements Plugin
{
    public static function make() : static
    {
        return app(static::class);
    }

    public static function get() : static
    {
        return filament(app(static::class)->getId());
    }

    public function getId() : string
    {
        return 'outerweb-filament-link-picker';
    }

    public function disableExternalLinks(bool|Closure $disableExternalLinks = true) : static
    {
        LinkPicker::disableExternalLinks($disableExternalLinks);

        return $this;
    }

    public function disableMailto(bool|Closure $disableMailto = true) : static
    {
        LinkPicker::disableMailto($disableMailto);

        return $this;
    }

    public function disableTel(bool|Closure $disableTel = true) : static
    {
        LinkPicker::disableTel($disableTel);

        return $this;
    }

    public function disableDownload(bool|Closure $disableDownload = true) : static
    {
        LinkPicker::disableDownload($disableDownload);

        return $this;
    }

    public function disableOpenInNewTab(bool|Closure $disableOpenInNewTab = true) : static
    {
        LinkPicker::disableOpenInNewTab($disableOpenInNewTab);

        return $this;
    }

    public function combineLocalizedRoutesUsing(Closure $callback) : static
    {
        LinkPicker::combineLocalizedRoutesUsing($callback);

        return $this;
    }

    public function buildLocalizedRouteUsing(Closure $callback) : static
    {
        LinkPicker::buildLocalizedRouteUsing($callback);

        return $this;
    }

    public function translateLabels(bool|Closure $translateLabels = true) : static
    {
        LinkPicker::translateLabels($translateLabels);

        return $this;
    }

    public function register(Panel $panel) : void
    {
        //
    }

    public function boot(Panel $panel) : void
    {
        //
    }
}
