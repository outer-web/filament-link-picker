# Changelog

All notable changes to `filament-link-picker` will be documented in this file.

## 2.0.0 - 2024-03-27

### Added

- Added better support for multiple locales by adding a `translateLabels()` method to the plugin.

### Fixed

- Rewrote the internal code to make routes still visible in the picker field when route:cache is ran.

## 1.4.0 - 2024-03-20

### Fixed

- Fixed issue where the `Link` entity could call `build()` on a null value.

## 1.3.0 - 2024-03-17

### Fixed

- Fixed bug with `localizedRoute()` parameter order.

## 1.2.5 - 2024-03-12

### Added

- Added support for Laravel 11.

## 1.2.4 - 2024-03-07

### Fixed

- Fixed a bug where link picker field was not being filled properly when it was already cast to a Link entity.

## 1.2.3 - 2024-03-07

### Fixed

- Remove `once` method because it is not available in L10.

## 1.2.2 - 2024-03-07

### Fixed

- Fixed required validation for the link picker field.

## 1.2.1 - 2024-03-02

### Fixed

- Fixed a bug with resetting form fields.

## 1.2.0 - 2024-03-02

### Fixed

- Fixed a bug where the livewire $container property was accessed before initialization. Fixed by injecting the state instead of using the getState() method.

## 1.1.0 - 2024-03-02

### Fixed

- Fixed LinkCast set and get method to work with json database columns.

## 1.0.0 - 2024-03-01

- Initial release
