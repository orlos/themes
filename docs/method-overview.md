<!---
title: Method overview
author: Caffeinated
-->


#### Common methods overview
Check out the API documentation for the full list of methods.

##### Themes (Facade => ThemeFactory)
 
| Function call | Return type | Description |
|:--------------|:------------|:------------|
| `Themes::setActive($theme)`   | self  | Set the active theme, `$theme` can be a Theme instance or the slug string of that theme |
| `Themes::getActive()`         | Theme | Returns the active theme |
| `Themes::setDefault($theme)`  | self  | Set the default theme, `$theme` can be a Theme instance or the slug string of that theme |
| `Themes::getDefault()`        | Theme | Returns the default theme |
| `Themes::resolveTheme($slug)` | Theme | Resolve a theme using it's slug. It will check all theme paths for the required theme. |
| `Themes::all()`               | string[] | Returns all resolved theme slugs |
| `Themes::get($slug)`          | Theme | Returns the theme instance if the theme is found |
| `Themes::has($slug)`          | bool  | Check if a theme exists |
| `Themes::count()`             | int   | Get the number of themes |
| `Themes::addNamespace($name, $dirName)`      | self   | Add a namespace to the theme |
| `Themes::getPath($type)`      | string   | Get a path for the type (assets, views, namespaces, packages) |
| `Themes::assetPath($key = null)`             | string   | Get the absolute filesystem path to an asset file |
| `Themes::assetUrl($key = null)`             | string   | Get the URL to an asset file |
| `Themes::assetUri($key = null)`             | string   | Get the URI to an asset file |


##### Theme (instance of a theme)
 
| Function call | Return type | Description |
|:--------------|:------------|:------------|
| `Theme::getConfig()`          | array  | The array from `theme.php` |
| `Theme::getParentTheme()`     | Theme  | .. |
| `Theme::getParentSlug()`      | string  | .. |
| `Theme::hasParent()`          | bool  | .. |
| `Theme::getSlug()`            | string  | .. |
| `Theme::getSlugKey()`         | string  | .. |
| `Theme::getSlugProvider()`    | string  | .. |
| `Theme::getName()`            | string  | .. |
| `Theme::isActive()`           | bool  | .. |
| `Theme::isDefault()`          | bool  | .. |
| `Theme::isBooted()`           | bool  | .. |
| `Theme::boot()`               | void  | .. |
| `Theme::getVersion()`         | SemVer  | .. |
| `Theme::getPath()`            | string  | .. |
| `Theme::getCascadedPath()`    | string  | .. |

