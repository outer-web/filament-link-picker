<?php

namespace Outerweb\FilamentLinkPicker\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;
use Outerweb\FilamentLinkPicker\Entities\LinkPickerRoute;
use Outerweb\FilamentLinkPicker\Facades\LinkPicker as FacadesLinkPicker;

class LinkPicker extends Field
{
    protected string $view = 'filament-link-picker::filament.forms.components.link-picker';

    protected bool|Closure $disableExternalLinks = false;

    protected bool|Closure $disableMailto = false;

    protected bool|Closure $disableTel = false;

    protected bool|Closure $disableDownload = false;

    protected bool|Closure $disableOpenInNewTab = false;

    public function disableExternalLinks(bool|Closure $disableExternalLinks = true): static
    {
        $this->disableExternalLinks = $disableExternalLinks;

        return $this;
    }

    public function disableMailto(bool|Closure $disableMailto = true): static
    {
        $this->disableMailto = $disableMailto;

        return $this;
    }

    public function disableTel(bool|Closure $disableTel = true): static
    {
        $this->disableTel = $disableTel;

        return $this;
    }

    public function disableDownload(bool|Closure $disableDownload = true): static
    {
        $this->disableDownload = $disableDownload;

        return $this;
    }

    public function disableOpenInNewTab(bool|Closure $disableOpenInNewTab = true): static
    {
        $this->disableOpenInNewTab = $disableOpenInNewTab;

        return $this;
    }

    public function getAllowsExternalLinks(): bool|Closure
    {
        if (!FacadesLinkPicker::getAllowsExternalLinks()) {
            return false;
        }

        return !(bool) $this->evaluate($this->disableExternalLinks);
    }

    public function getAllowsMailto(): bool|Closure
    {
        if (!FacadesLinkPicker::getAllowsMailto()) {
            return false;
        }

        return !(bool) $this->evaluate($this->disableMailto);
    }

    public function getAllowsTel(): bool|Closure
    {
        if (!FacadesLinkPicker::getAllowsTel()) {
            return false;
        }

        return !(bool) $this->evaluate($this->disableTel);
    }

    public function getAllowsDownload(): bool|Closure
    {
        if (!FacadesLinkPicker::getAllowsDownload()) {
            return false;
        }

        return !(bool) $this->evaluate($this->disableDownload);
    }

    public function getAllowsOpenInNewTab(): bool|Closure
    {
        if (!FacadesLinkPicker::getAllowsOpenInNewTab()) {
            return false;
        }

        return !(bool) $this->evaluate($this->disableOpenInNewTab);
    }

    public function getRoutes(bool $grouped = false): array
    {
        return collect(FacadesLinkPicker::getRoutes())
            ->when($grouped, fn($routes) => $routes->groupBy('group'))
            ->sortKeys()
            ->toArray();
    }

    public function getRouteLabel(LinkPickerRoute $route): string
    {
        if (is_null($route->label)) {
            return collect(explode('.', $route->name))
                ->map(fn($part) => Str::title($part))
                ->implode(' > ');
        }

        return $route->label;
    }

    public function getSelectedRoute(): ?LinkPickerRoute
    {
        return collect($this->getRoutes())
            ->firstWhere('name', $this->getState()['route_name']);
    }

    public function getSelectedRouteHasParameters(): bool
    {
        return count($this->getSelectedRouteParameters()) > 0;
    }

    public function getSelectedRouteParameters(): array
    {
        return $this->getSelectedRoute()?->getRouteParameters()?->toArray() ?? [];
    }

    public function getRouteNameOptions(): array
    {
        return collect($this->getRoutes(grouped: true))
            ->mapWithKeys(function ($routes, $group) {
                if (blank($group)) {
                    $group = __('filament-link-picker::translations.route_groups.internal');
                }

                return [
                    $group => collect($routes)->mapWithKeys(function (LinkPickerRoute $route) {
                        return [$route->name => $this->getRouteLabel($route)];
                    })->toArray(),
                ];
            })
            ->toArray();
    }

    public function makeFieldFromParameter(object $parameter): Field
    {
        $name = "parameters.{$parameter->name}";

        return match ($parameter->type) {
            'url' => TextInput::make($name)
                ->label($parameter->label)
                ->required($parameter->is_required)
                ->url(),
            'email' => TextInput::make($name)
                ->label($parameter->label)
                ->required($parameter->is_required)
                ->email(),
            'tel' => TextInput::make($name)
                ->label($parameter->label)
                ->required($parameter->is_required)
                ->tel(),
            'select' => Select::make($name)
                ->label($parameter->label)
                ->options($parameter->options)
                ->required($parameter->is_required),
            default => TextInput::make($name)
                ->label($parameter->label)
                ->required($parameter->is_required),
        };
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            Fieldset::make()
                ->columns(1)
                ->schema([
                    Select::make('route_name')
                        ->label(__('filament-link-picker::translations.forms.labels.route_name'))
                        ->options($this->getRouteNameOptions())
                        ->nullable()
                        ->live()
                        ->afterStateUpdated(function (Select $component) {
                            // Reset the parameters when the route changes.
                            $state = $this->getState();
                            $state['parameters'] = [];
                            $this->state($state);

                            if ($fieldset = $component->getContainer()->getComponent('parameters')) {
                                $fieldset->getChildComponentContainer()
                                    ->fill();
                            }
                        }),

                    Fieldset::make(__('filament-link-picker::translations.forms.labels.parameters'))
                        ->hidden(fn() => !$this->getSelectedRouteHasParameters())
                        ->schema(fn() => collect($this->getSelectedRouteParameters())
                            ->map(fn(object $parameter) => $this->makeFieldFromParameter($parameter))
                            ->toArray()),

                    Fieldset::make(__('filament-link-picker::translations.forms.labels.options'))
                        ->hidden(
                            fn() =>
                            is_null($this->getSelectedRoute()) ||
                            !(
                                $this->getAllowsDownload() &&
                                $this->getAllowsOpenInNewTab()
                            )
                        )
                        ->schema([
                            Toggle::make('options.is_download')
                                ->label(__('filament-link-picker::translations.forms.labels.is_download'))
                                ->hidden(fn() => !$this->getAllowsDownload()),

                            Toggle::make('options.opens_in_new_tab')
                                ->label(__('filament-link-picker::translations.forms.labels.opens_in_new_tab'))
                                ->hidden(fn() => !$this->getAllowsOpenInNewTab()),
                        ]),
                ]),
        ]);

        $this->live(debounce: 500);

        $this->registerActions([
            fn(self $component): Action => $component->getEditAction(),
        ]);

        $this->afterStateHydrated(static function (self $component, $state): void {
            $component->state(FacadesLinkPicker::dataToLinkEntity($state)->toArray());
        });

        $this->dehydrateStateUsing = $this->dehydrateStateUsing ?? function ($state) {
            return FacadesLinkPicker::dataToLinkEntity($state)->toArray();
        };
    }

    public function getState(): array
    {
        $state = parent::getState();

        if (!$this->getAllowsDownload()) {
            unset($state['options']['is_download']);
        }

        if (!$this->getAllowsOpenInNewTab()) {
            unset($state['options']['opens_in_new_tab']);
        }

        return FacadesLinkPicker::dataToLinkEntity($state)->toArray();
    }
}
