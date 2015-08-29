<!---
title: Using themes
author: Caffeinated
-->

### Creating a theme
Inside the defined themes directories you can add themes. Themes have a `$vendor / $package` structure, like composer's vendor files.
The default theme folder is `public/themes`. To add a theme, create 2 directories matching slug you want to give the theme. An example:

Create `public/themes/backend/admin` and also add a `theme.php` file in that directory. `theme.php` contains configuration and optional callbacks:  
```php
use Illuminate\Contracts\Foundation\Application;
use Laradic\Themes\Contracts\ThemeFactory;
return array(
    'slug' => 'backend/admin',
    'name' => 'Admin theme',
    'version' => '1.0.0',
    'parent' => null,               // optional parent theme slug
    'register' => function(Application $app, ThemeFactory $themes){
        // optional register callback. Will always be called.    
    },
    'boot' => function(Application $app, ThemeFactory $themes){
        // optional boot callback. Will be called when the theme is activated and used
    }
);
```

### Loading views
The active and default theme can be set in the configuration by altering the `active` and `default` keys.  
You can set the active theme on the fly by using `Theme::setActive('theme/slug')`.  
You can set the default theme on the fly by using `Theme::setDefault('theme/slug')`.  

```php
// public/themes/{active/theme}/views/view-file.EXT
$view = View::make("view-file");

// public/themes/{active/theme}/namespaces/my-namespace/views/view-file.EXT
$view = View::make("my-namespace::view-file");

// public/themes/{active/theme}/packages/vendor-name/package-name/views/view-file.EXT
$view = View::make("vendor-name/package-name::view-file");

Themes::setActive("backend/admin");
$view = View::make("view-file"); // -> public/backend/admin/views/view-file.EXT
// etc
```

### Assets
Basic asset path resolving functionality is included and explained below. 
If you require more power, the `caffeinated/bonsai` package is an Asset management package that integrates tightly into
`caffeinated/themes`. It provides
dependency management, pre-processing, grouping & caching, minification, you name it. Highly recommend 
using it together with `caffeinated/themes`.

By default, the Themes package provides several methods which work similair as `View::make` to load your assets **with cascaded inheritance**

```php
// Getting the absolute file path to a asset
Themes::assetPath("assetFile.js"); // ...path/public/themes/{active/theme}/assets/assetFile.js

// Getting the URL for a asset
Themes::assetUrl("assetFile.js"); // http://yourhost.com/themes/{active/theme}/assets/assetFile.js

// getting the URI for a asset
Themes::assetUri("assetFile.js"); // themes/{active/theme}/assets/assetFile.js

// Using namespace and packages, similair to views. Works with all 3 methods (assetPath, assetUrl, assetUri)
Themes::assetUrl("my-namespace::assetFile.js");
Themes::assetUrl("backend/admin::assetFile.js");
```

### More
For more & in-depth functionality check the [Method overview](method-overview.md) document.
