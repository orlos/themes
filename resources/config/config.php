<?php

return array(
    /* paths */
    'active'          => 'frontend/example',
    'default'         => 'frontend/default',
    /* Class names */
    'themeClass'      => '\\Caffeinated\\Themes\\Theme',
    'paths'           => array(
        'themes'     => array(
            public_path('themes'),
            public_path()
        ),
        // These paths are relative to the theme path defined above
        'namespaces' => 'namespaces',
        'packages'   => 'packages',
        'views'      => 'views',    //default ex: public/themes/{area}/{theme}/views
        'assets'     => 'assets',
        // full path to cache folder, requires to be public
        'cache'      => public_path('cache')
    )
);
