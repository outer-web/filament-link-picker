<?php

namespace Outerweb\FilamentLinkPicker\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Outerweb\FilamentLinkPicker\Contracts\HasLinkPickerOptions;
use Outerweb\FilamentLinkPicker\Facades\LinkPicker;
use ReflectionClass;
use ReflectionParameter;

class LinkPickerRoute
{
    public string $original_name;

    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public ?string $group = null,
        public bool $is_localized = false,
        public array $parameterLabels = [],
        public array $parameterOptions = [],
    ) {
        $this->original_name = $name;
    }

    public static function make(
        ?string $name = null,
        ?string $label = null,
        ?string $group = null,
        bool $isLocalized = false,
        array $parameterLabels = [],
        array $parameterOptions = [],
    ): static {
        $instance = new static($name, $label, $group, $isLocalized, $parameterLabels, $parameterOptions);

        $instance->original_name = $name;

        return $instance;
    }

    public function name(?string $name, bool $overrideOriginalName = false): static
    {
        $this->name = $name;

        if ($overrideOriginalName) {
            $this->original_name = $name;
        }

        return $this;
    }

    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function group(?string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function isLocalized(bool $isLocalized = true): static
    {
        $this->is_localized = $isLocalized;

        return $this;
    }

    public function getRoute(): ?RoutingRoute
    {
        return Route::getRoutes()->getByName($this->original_name);
    }

    public function getRouteParameters(): Collection
    {
        if (is_null($this->getRoute()) && Str::startsWith($this->original_name, 'external.')) {
            return match (Str::after($this->original_name, 'external.')) {
                'link' => collect([
                    RouteParameter::make(
                        name: 'url',
                        label: __('filament-link-picker::translations.route_parameters.url'),
                        type: 'url',
                        isRequired: true
                    ),
                ]),
                'mailto' => collect([
                    RouteParameter::make(
                        name: 'email',
                        label: __('filament-link-picker::translations.route_parameters.email'),
                        type: 'email',
                        isRequired: true
                    ),
                ]),
                'tel' => collect([
                    RouteParameter::make(
                        name: 'tel',
                        label: __('filament-link-picker::translations.route_parameters.tel'),
                        type: 'tel',
                        isRequired: true
                    ),
                ]),
                default => collect(),
            };
        }

        $route = $this->getRoute();
        $signatureParameters = collect($route->signatureParameters());

        return once(function () use ($route, $signatureParameters) {
            return collect($route->parameterNames())
                ->map(function (string $parameter) use ($signatureParameters, $route) {
                    $model = null;
                    $isRequired = false;
                    $modelRouteKeyName = null;

                    if ($signatureParameters->contains('name', $parameter)) {
                        /** @var ReflectionParameter $reflectionParameter */
                        $reflectionParameter = $signatureParameters->firstWhere('name', $parameter);

                        $model = $reflectionParameter?->getType()?->getName();
                        $modelRouteKeyName = $route->bindingFieldFor($parameter) ?? $model::$primaryKey ?? 'id';
                        $isRequired = $reflectionParameter ? !$reflectionParameter->allowsNull() : false;
                    }

                    $parameterOptions = $this->getRouteParameterOptions($parameter, $model);

                    return RouteParameter::make(
                        $parameter,
                        $this->parameterLabels[$parameter] ?? Str::title($parameter),
                        count($parameterOptions) ? 'select' : 'text',
                        $model,
                        $modelRouteKeyName,
                        $isRequired,
                        $this->getRouteParameterOptions($parameter, $model),
                    );
                });
        });
    }

    public function getRouteParameterOptions(string $parameter, ?string $model): array
    {
        if (isset($this->parameterOptions[$parameter])) {
            return $this->parameterOptions[$parameter];
        }

        if ($model) {
            $class = new ReflectionClass($model);

            return $model::query()
                ->when(method_exists($class, 'scopeLinkPickerOptions'), function (Builder $query) {
                    return $query->linkPickerOptions();
                })
                ->get()
                ->mapWithKeys(function (Model $model) {
                    $label = $this->getRouteParameterLabel($model);

                    return [$model->getKey() => $label];
                })
                ->toArray();
        }

        return [];
    }

    public function getRouteParameterLabel(Model $model): string
    {
        $label = null;

        if (method_exists($model, 'getLinkPickerLabel')) {
            $label = $model->getLinkPickerLabel();
        } elseif (property_exists($model, 'linkPickerLabelKey')) {
            $label = $model->{$model->linkPickerLabelKey};
        } else {
            $label = $model->label ?? $model->name ?? $model->title;
        }

        if (is_null($label)) {
            $modelClass = $model::class;
            throw new \Exception("Outerweb\FilamentLinkPicker: Could not automatically determine a label for the model [{$modelClass}]. Please implement the HasLinkPickerOptions interface on your model or provide a custom parameterOptions array on the route itself.");
        }

        return $label;
    }

    public function build(array $parameters = [], bool $absolute = false, ?string $locale = null): ?string
    {
        if (Str::startsWith($this->original_name, 'external.')) {
            return match (Str::after($this->original_name, 'external.')) {
                'link' => $parameters['url'],
                'mailto' => "mailto:{$parameters['email']}",
                'tel' => "tel:{$parameters['tel']}",
                default => '',
            };
        }

        $parameters = $this->castRouteParameters($parameters);

        if ($this->is_localized) {
            return LinkPicker::buildLocalizedRoute($this->name, $parameters, $absolute, $locale);
        }

        return route($this->original_name, $parameters, $absolute);
    }

    public function castRouteParameters(array $parameters): array
    {
        $routeParameters = $this->getRouteParameters();

        return collect($parameters)
            ->map(function ($value, $key) use ($routeParameters) {
                $routeParameter = $routeParameters->firstWhere('name', $key);

                if ($routeParameter) {
                    return $this->castRouteParameter($routeParameter, $value);
                }

                return $value;
            })
            ->toArray();
    }

    public function castRouteParameter(RouteParameter $routeParameter, $value): string
    {
        if (!is_null($routeParameter->model)) {
            return (string) $routeParameter->model::query()
                ->find($value)?->{$routeParameter->modelRouteKeyName} ?? $value;
        }

        return (string) $value;
    }
}
