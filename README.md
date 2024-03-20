# Filament Link Picker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/outerweb/filament-link-picker.svg?style=flat-square)](https://packagist.org/packages/outerweb/filament-link-picker)
[![Total Downloads](https://img.shields.io/packagist/dt/outerweb/filament-link-picker.svg?style=flat-square)](https://packagist.org/packages/outerweb/filament-link-picker)

This package adds a field to pick a link from your defined routes or external links.
It also adds a blade component to render the links.

## Features

The link picker field will show the following options:

- A list of all your application routes that are marked for the link picker to discover.
- An external link option to add a link to an external website. (Can be disabled)
- An email link option to add a mailto link. (Can be disabled)
- A telephone link option to add a tel link. (Can be disabled)

The link picker will automatically show and bind the parameters of the selected route. This includes:

- A select field powered by route model binding to automatically show the available models.
- A text input field for all other parameters.
- An url input field for the external link option.
- An email input field for the email link option.
- A tel input field for the telephone link option.

The link picker can also show route options:

- `is_download` to specify if the link is a download link.
- `opens_in_new_tab` to specify if the link should open in a new tab.

The link can be rendered using the `<x-filament-link-picker-link />` blade component.

## Installation

You can install the package via composer:

```bash
composer require outerweb/filament-link-picker
```

Add the plugin to your desired Filament panel:

```php
use OuterWeb\FilamentLinkPicker\Filament\FilamentLinkPicker;

class FilamentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugins([
                FilamentLinkPickerPlugin::make(),
            ]);
    }
}
```

## Configuration

### Disabling the external link option

You can disable the external link option by adding the `disableExternalLinks` method to the plugin.

```php
FilamentLinkPickerPlugin::make()
    ->disableExternalLinks();
```

### Disabling the email link option

You can disable the email link option by adding the `disableMailto` method to the plugin.

```php
FilamentLinkPickerPlugin::make()
    ->disableMailto();
```

### Disabling the telephone link option

You can disable the telephone link option by adding the `disableTel` method to the plugin.

```php
FilamentLinkPickerPlugin::make()
    ->disableTel();
```

### Disabling the 'download' option

You can disable the 'download' option by adding the `disableDownload` method to the plugin.

```php
FilamentLinkPickerPlugin::make()
    ->disableDownload();
```

### Disabling the 'opens in new tab' option

You can disable the 'opens in new tab' option by adding the `disableOpenInNewTab` method to the plugin.

```php
FilamentLinkPickerPlugin::make()
    ->disableOpenInNewTab();
```

## Usage

### Setting up routes

You can mark a route for the link picker to discover by adding the `filamentLinkPicker()` method to the route.

```php
use Illuminate\Support\Facades\Route;

Route::get('/your-route', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker();
```

#### Customizing the route's label

The label of the route will be used in the dropdown of the link picker. You can customize the label by passing it as an argument to the `filamentLinkPicker()` method.

```php
Route::get('/your-route', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker(
        label: 'Your custom label'
    );
```

By default, the label will be the route's name. If the route name contains dots, they will be replaced by '>'.

#### Customizing the route's group

The group will be used to group the routes in the dropdown of the link picker. You can customize the group by passing it as an argument to the `filamentLinkPicker()` method.

```php
Route::get('/your-route', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker(
        group: 'Your custom group'
    );
```

#### Marking the route as 'localized'

Marking a route as 'localized' will make the link picker combine all localized versions of that route. This is useful when you have a multi-language where you have the same route for different languages so that the link picker does not show the same route multiple times. See the [Localization](#localization) section for more information.

```php
Route::get('/your-route', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker(
        isLocalized: true
    );
```

#### Defining specific parameter labels

You can define specific parameter labels for the route by passing an array to the `filamentLinkPicker()` method.
These labels will be used in the link picker as labels for the parameter input fields.

```php
Route::get('/your-route/{parameter}', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker(
        parameterLabels: [
            'your-parameter' => 'Your parameter label'
        ]
    );
```

#### Defining specific parameter options

You can define specific parameter options for the route by passing an array to the `filamentLinkPicker()` method.
These options will be used in the link picker as options for the parameter select field.

```php
Route::get('/your-route/{parameter}', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker(
        parameterOptions: [
            'your-parameter' => [
                'option1' => 'Option 1',
                'option2' => 'Option 2',
            ]
        ]
    );
```

### Setting up route model binding

Route model binding is supported by default. The link picker will automatically show the available models in the parameter select field. It will save the primary key of the model to the database.

By default, we go through the following attributes to automatically find the model's label:

- label
- name
- title

If none of these attributes are found, the application will throw an exception.

#### Explicitly defining the model's label

You can explicitly define the model's label by adding a `getLinkPickerLabel` method to the model.

```php
class YourModel extends Model
{
    public function getLinkPickerLabel(): string
    {
        return $this->your_attribute;
    }
}
```

Or you can define the label as a property on the model.

```php
class YourModel extends Model
{
    public string $linkPickerLabel = 'your_attribute';
}
```

#### Filtering the available models

You can filter the available models by adding a `scopeLinkPickerOptions` method to the model.
This scope will then be applied to the model's query when fetching the available options for the select field.

```php
class YourModel extends Model
{
    public function scopeLinkPickerOptions(Builder $query): void
    {
        // Your query here
    }
}
```

### Localization

The link picker can handle localized routes well. To do so, follow these steps:

1. Mark the route as 'localized' by adding the `localized` argument to the `filamentLinkPicker()` method on your route.

```php
Route::get('/your-route', YourController::class)
    ->name('your-route')
    ->filamentLinkPicker(
        isLocalized: true
    );
```

2. Configure how the link picker should combine the localized routes by adding the `combineLocalizedRoutesUsing` method to the plugin.

```php
use Outerweb\FilamentLinkPicker\Entities\LinkPickerRoute;

FilamentLinkPickerPlugin::make()
    ->combineLocalizedRoutesUsing(function (LinkPickerRoute $route): LinkPickerRoute {
        // Imagine your routes are named like `en.your-route` and `nl.your-route`
        // We want to combine these routes so that the link picker only shows one option for `your-route`
        return $route->name(Str::after($route->name(), '.'));
    });
```

By default, the package will combine the localized routes by removing the part of the name before the first dot. If you want to use this behavior, you do not need to specify the `combineLocalizedRoutesUsing` method.

3. Configure how the link picker should build the localized routes by adding the `buildLocalizedRouteUsing` method to the plugin.

```php
use Outerweb\FilamentLinkPicker\Entities\LinkPickerRoute;

FilamentLinkPickerPlugin::make()
    ->buildLocalizedRouteUsing(function (string $name, array $parameters = [], bool $absolute = true, ?string $locale = null): ?string {
        // If you use our `outerweb/localization` package,
        // You can use the `localizedRoute` helper.
        return localizedRoute($name, $parameters, $absolute, $locale);
    });
```

As a fallback, when you do not specify a callback function, the link picker will use the `outerweb/localization` package to build the localized routes if it is installed. This package provides a `localizedRoute` helper that you can use in your application. Read more about this package [here](https://github.com/outer-web/localization).

### Using the actual value

To use the actual value of the link, you can cast the attribute in your model or use the facade.

#### Casting

You can cast your model's attribute to the `Outerweb\FilamentLinkPicker\Entities\Link` class to take advantage of the entity's properties and methods.

```php
use Outerweb\FilamentLinkPicker\Casts\LinkCast;

class YourModel extends Model
{
    protected $casts = [
        'link' => LinkCast::class,
    ];
}
```

#### Using the facade

You can use the `Outerweb\FilamentLinkPicker\Facades\LinkPicker` facade to cast the value to a `Link` entity.

```php
use Outerweb\FilamentLinkPicker\Facades\LinkPicker;

$link = LinkPicker::dataToLinkEntity(array $data);
```

### Rendering the link

You can use the `<x-filament-link-picker-link />` blade component to render the link.

```blade
<x-filament-link-picker-link :link="$link">
    Your label here
</x-filament-link-picker-link>
```

The link picker options like `is_download` and `opens_in_new_tab` will only take effect when you do not specify them on the component itself. If you do specify them, the component will use the specified values.

```blade
<x-filament-link-picker-link :link="$link" download target="_self" />
```

Here, we specified the `download` and `target` attributes. The component will use these values instead of the ones from the link picker.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Simon Broekaert](https://github.com/SimonBroekaert)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
