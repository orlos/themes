Caffeinated Themes v3
=====================
[![Laravel 5.1](https://img.shields.io/badge/Laravel-5.1-orange.svg?style=flat-square)](http://laravel.com)
[![Build Status](https://img.shields.io/travis/caffeinated/themes.svg?branch=v3&style=flat-square)](https://travis-ci.org/caffeinated/themes)
[![Scrutinizer coverage](https://img.shields.io/scrutinizer/coverage/g/caffeinated/themes/v3.svg?&style=flat-square)](https://scrutinizer-ci.com/g/caffeinated/themes/?branch=v3)
[![Scrutinizer quality](https://img.shields.io/scrutinizer/g/caffeinated/themes/v3.svg?&style=flat-square)](https://scrutinizer-ci.com/g/caffeinated/themes/?branch=v3)
[![Source](http://img.shields.io/badge/source-caffeinated/themes-blue.svg?style=flat-square)](https://github.com/caffeinated/themes)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Caffeinated Themes providing multi-theme inherited cascading support. This gives an easy way to further decouple the way your web application looks from your code base.

The package follows the FIG standards PSR-1, PSR-2, and PSR-4 to ensure a high level of interoperability between shared PHP code. At the moment the package is not unit tested, but is planned to be covered later down the road.

Features
--------
- View engine agnostic. Works with any template language (Blade, Twig, Smarty, etc)
- Supports Caffeinated Modules
- Supports both the Blade and Twig templating engines
- Multi-theme inherited cascading support
- Theme components, easily create re-usable UI components

Documentation
-------------
You will find user friendly and updated documentation in the wiki here: [Caffeinated Themes Wiki](https://github.com/caffeinated/themes/wiki)

Quick Installation
------------------
```
composer require caffeinated/themes=~3.0
```

Once this operation is complete, simply add both the service provider and facade classes to your project's `config/app.php` file:

### Service Provider
```php
Caffeinated\Themes\ThemesServiceProvider::class,
```

### Facade
```php
'Themes' => Caffeinated\Themes\Facades\Themes::class,
```

And that's it! With your coffee in reach, start building some awesome themes!
