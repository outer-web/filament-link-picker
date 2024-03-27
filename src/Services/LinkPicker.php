<?php

namespace Outerweb\FilamentLinkPicker\Services;

use Closure;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Outerweb\FilamentLinkPicker\Entities\Link;
use Outerweb\FilamentLinkPicker\Entities\LinkPickerRoute;

class LinkPicker
{
    public const ROUTE_EXTERNAL_LINK = 'external.link';

    public const ROUTE_MAILTO = 'external.mailto';

    public const ROUTE_TEL = 'external.tel';

    protected array $routes = [];

    protected bool|Closure $disableExternalLinks = false;

    protected bool|Closure $disableMailto = false;

    protected bool|Closure $disableTel = false;

    protected bool|Closure $disableDownload = false;

    protected bool|Closure $disableOpenInNewTab = false;

    protected ?Closure $combineLocalizedRoutesUsing = null;

    protected ?Closure $buildLocalizedRouteUsing = null;

    protected bool|Closure $translateLabels = false;

    public function getExternalLinkRoute() : LinkPickerRoute
    {
        return LinkPickerRoute::make(
            self::ROUTE_EXTERNAL_LINK,
            __('filament-link-picker::translations.route_names.external.link'),
            __('filament-link-picker::translations.route_groups.external'),
        );
    }

    public function getMailtoRoute() : LinkPickerRoute
    {
        return LinkPickerRoute::make(
            self::ROUTE_MAILTO,
            __('filament-link-picker::translations.route_names.external.mailto'),
            __('filament-link-picker::translations.route_groups.external'),
        );
    }

    public function getTelRoute() : LinkPickerRoute
    {
        return LinkPickerRoute::make(
            self::ROUTE_TEL,
            __('filament-link-picker::translations.route_names.external.tel'),
            __('filament-link-picker::translations.route_groups.external'),
        );
    }

    public function addRoute(LinkPickerRoute $route, bool $override = false) : static
    {
        if ($this->routes[$route->name] ?? false && ! $override) {
            return $this;
        }

        $this->routes[$route->name] = $route;

        return $this;
    }

    public function disableExternalLinks(bool|Closure $disableExternalLinks = true) : static
    {
        $this->disableExternalLinks = $disableExternalLinks;

        return $this;
    }

    public function disableMailto(bool|Closure $disableMailto = true) : static
    {
        $this->disableMailto = $disableMailto;

        return $this;
    }

    public function disableTel(bool|Closure $disableTel = true) : static
    {
        $this->disableTel = $disableTel;

        return $this;
    }

    public function disableDownload(bool|Closure $disableDownload = true) : static
    {
        $this->disableDownload = $disableDownload;

        return $this;
    }

    public function disableOpenInNewTab(bool|Closure $disableOpenInNewTab = true) : static
    {
        $this->disableOpenInNewTab = $disableOpenInNewTab;

        return $this;
    }

    public function translateLabels(bool|Closure $translateLabels = true) : static
    {
        $this->translateLabels = $translateLabels;

        return $this;
    }

    public function registerApplicationRoutes() : void
    {
        collect(Route::getRoutes())
            ->each(function (RoutingRoute $route) {
                $data = $route->getAction('linkPickerRoute');

                if (! $data) {
                    return;
                }

                $this->addRoute(LinkPickerRoute::make(
                    $data['routeName'] ?? $route->getName(),
                    $data['label'] ?? null,
                    $data['group'] ?? null,
                    $data['isLocalized'] ?? false,
                    $data['parameterLabels'] ?? [],
                    $data['parameterOptions'] ?? []
                ));
            });
    }

    public function getRoutes() : array
    {
        $this->registerApplicationRoutes();

        if ($this->getTranslateLabels()) {
            $this->routes = collect($this->routes)
                ->map(function (LinkPickerRoute $route) {
                    $route->label(__($route->label));
                    $route->group(__($route->group));
                    $route->parameterLabels(collect($route->parameterLabels)
                        ->map(function ($label) {
                            return __($label);
                        })
                        ->toArray()
                    );

                    return $route;
                })
                ->toArray();
        }

        return $this->combineLocalizedRoutes($this->routes);
    }

    public function getRouteByName(?string $name) : ?LinkPickerRoute
    {
        if (is_null($name)) {
            return null;
        }

        return $this->getRoutes()[$name] ?? null;
    }

    public function getAllowsExternalLinks() : bool|Closure
    {
        return ! ($this->disableExternalLinks instanceof Closure
            ? call_user_func($this->disableExternalLinks)
            : $this->disableExternalLinks);
    }

    public function getAllowsMailto() : bool|Closure
    {
        return ! ($this->disableMailto instanceof Closure
            ? call_user_func($this->disableMailto)
            : $this->disableMailto);
    }

    public function getAllowsTel() : bool|Closure
    {
        return ! ($this->disableTel instanceof Closure
            ? call_user_func($this->disableTel)
            : $this->disableTel);
    }

    public function getAllowsDownload() : bool|Closure
    {
        return ! ($this->disableDownload instanceof Closure
            ? call_user_func($this->disableDownload)
            : $this->disableDownload);
    }

    public function getAllowsOpenInNewTab() : bool|Closure
    {
        return ! ($this->disableOpenInNewTab instanceof Closure
            ? call_user_func($this->disableOpenInNewTab)
            : $this->disableOpenInNewTab);
    }

    public function getTranslateLabels() : bool|Closure
    {
        return $this->translateLabels;
    }

    public function combineLocalizedRoutesUsing(Closure $closure) : static
    {
        $this->combineLocalizedRoutesUsing = $closure;

        return $this;
    }

    public function buildLocalizedRouteUsing(Closure $closure) : static
    {
        $this->buildLocalizedRouteUsing = $closure;

        return $this;
    }

    public function combineLocalizedRoutes(array $routes) : array
    {
        return collect($routes)
            ->mapWithKeys(function (LinkPickerRoute $route, string $key) {
                $route = clone $route;

                if (! $route->is_localized) {
                    return [$key => $route];
                }

                if ($this->combineLocalizedRoutesUsing instanceof Closure) {
                    $route = call_user_func($this->combineLocalizedRoutesUsing, $route);

                    return [$route->name => $route];
                }

                $route->name(Str::after($route->name, '.'));

                return [$route->name => $route];
            })
            ->unique(fn (LinkPickerRoute $route) => $route->name)
            ->toArray();
    }

    public function buildLocalizedRoute(string $name, array $parameters = [], bool $absolute = true, ?string $locale = null) : ?string
    {
        if ($this->buildLocalizedRouteUsing instanceof Closure) {
            return call_user_func($this->buildLocalizedRouteUsing, $name, $parameters, $absolute, $locale);
        }

        if (function_exists('localizedRoute')) {
            // Support `outerweb/localization` package by default
            return localizedRoute($name, $parameters, $absolute, $locale);
        }

        return null;
    }

    public function dataToLinkEntity(array|string|Link|null $data) : Link
    {
        if ($data instanceof Link) {
            return $data;
        }

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return Link::makeFromArray($data)->cleanUpParameters();
    }
}
