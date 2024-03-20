<?php

namespace Outerweb\FilamentLinkPicker\Entities;

use Outerweb\FilamentLinkPicker\Facades\LinkPicker;

class Link
{
    public function __construct(
        public ?string $route_name = null,
        public array $parameters = [],
        public array $options = [],
    ) {
        //
    }

    public static function make(
        ?string $route_name = null,
        array $parameters = [],
        array $options = [],
    ) : static {
        return new static($route_name, $parameters, $options);
    }

    public static function makeFromArray(?array $data) : static
    {
        if (is_null($data)) {
            return new static();
        }

        return new static(
            $data['route_name'] ?? null,
            $data['parameters'] ?? [],
            $data['options'] ?? [],
        );
    }

    public function __get(string $key) : mixed
    {
        return $this->options[$key] ?? null;
    }

    public function toArray() : array
    {
        return [
            'route_name' => $this->route_name,
            'parameters' => $this->parameters,
            'options' => $this->options,
        ];
    }

    public function cleanUpParameters() : self
    {
        $routeParameters = LinkPicker::getRouteByName($this->route_name)?->getRouteParameters() ?? [];

        $this->parameters = collect($this->parameters)
            ->filter(function ($value, $key) use ($routeParameters) {
                return collect($routeParameters)->where('name', $key)->isNotEmpty();
            })
            ->toArray();

        return $this;
    }

    public function build($absolute = true, ?string $locale = null) : ?string
    {
        return LinkPicker::getRouteByName($this->route_name)?->build($this->parameters, $absolute, $locale);
    }
}
