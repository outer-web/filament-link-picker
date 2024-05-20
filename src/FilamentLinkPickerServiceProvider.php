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

        Route::macro(
            'filamentLinkPicker',
            function (?string $label = null, ?string $group = null, bool $isLocalized = false, array $parameterLabels = [], array $parameterOptions = [], array $parameterModelKeys = []) {
                /** @var Route $route */
                $route = $this;

                $route->action['linkPickerRoute'] = [
                    'routeName' => $route->getName(),
                    'label' => $label,
                    'group' => $group,
                    'isLocalized' => $isLocalized,
                    'parameterLabels' => $parameterLabels,
                    'parameterOptions' => $parameterOptions,
                    'parameterModelKeys' => $parameterModelKeys
                ];
            }
        );
    }
}
