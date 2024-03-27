<?php

namespace Outerweb\FilamentLinkPicker;

use Illuminate\Routing\Route;
use Outerweb\FilamentLinkPicker\Components;
use Outerweb\FilamentLinkPicker\Entities\LinkPickerRoute;
use Outerweb\FilamentLinkPicker\Facades\LinkPicker as FacadesLinkPicker;
use Outerweb\FilamentLinkPicker\Services\LinkPicker;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentLinkPickerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package) : void
    {
        $package
            ->name('filament-link-picker')
            ->hasViews()
            ->hasViewComponents(
                'filament-link-picker',
                Components\Link::class,
            )
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $composerFile = file_get_contents(__DIR__ . '/../composer.json');

                if ($composerFile) {
                    $githubRepo = json_decode($composerFile, true)['homepage'] ?? null;

                    if ($githubRepo) {
                        $command
                            ->askToStarRepoOnGitHub($githubRepo);
                    }
                }
            });
    }

    public function boot()
    {
        parent::boot();

        $this->app->singleton(LinkPicker::class, function () {
            return new LinkPicker();
        });

        if (FacadesLinkPicker::getAllowsExternalLinks()) {
            FacadesLinkPicker::addRoute(
                LinkPickerRoute::make(
                    LinkPicker::ROUTE_EXTERNAL_LINK,
                    FacadesLinkPicker::getTranslateLabels()
                    ? 'filament-link-picker::translations.route_names.external.link'
                    : __('filament-link-picker::translations.route_names.external.link'),
                    FacadesLinkPicker::getTranslateLabels()
                    ? 'filament-link-picker::translations.route_groups.external'
                    : __('filament-link-picker::translations.route_groups.external')
                )
            );
        }

        if (FacadesLinkPicker::getAllowsMailto()) {
            FacadesLinkPicker::addRoute(
                LinkPickerRoute::make(
                    LinkPicker::ROUTE_MAILTO,
                    FacadesLinkPicker::getTranslateLabels()
                    ? 'filament-link-picker::translations.route_names.external.mailto'
                    : __('filament-link-picker::translations.route_names.external.mailto'),
                    FacadesLinkPicker::getTranslateLabels()
                    ? 'filament-link-picker::translations.route_groups.external'
                    : __('filament-link-picker::translations.route_groups.external')
                )
            );
        }

        if (FacadesLinkPicker::getAllowsTel()) {
            FacadesLinkPicker::addRoute(
                LinkPickerRoute::make(
                    LinkPicker::ROUTE_TEL,
                    FacadesLinkPicker::getTranslateLabels()
                    ? 'filament-link-picker::translations.route_names.external.tel'
                    : __('filament-link-picker::translations.route_names.external.tel'),
                    FacadesLinkPicker::getTranslateLabels()
                    ? 'filament-link-picker::translations.route_groups.external'
                    : __('filament-link-picker::translations.route_groups.external')
                )
            );
        }

        Route::macro(
            'filamentLinkPicker',
            function (?string $label = null, ?string $group = null, bool $isLocalized = false, array $parameterLabels = [], array $parameterOptions = []) {
                /** @var Route $route */
                $route = $this;

                FacadesLinkPicker::addRoute(
                    LinkPickerRoute::make(
                        $route->getName(),
                        $label,
                        $group,
                        $isLocalized,
                        $parameterLabels,
                        $parameterOptions
                    )
                );
            }
        );
    }
}
