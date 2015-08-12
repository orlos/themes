<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes;

use Illuminate\Foundation\Application;
use Illuminate\View\FileViewFinder;
use Caffeinated\Beverage\ServiceProvider;

/**
 * This is the ThemeServiceProvider.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemeServiceProvider extends ServiceProvider
{

    /**
     * @var string
     */
    protected $dir = __DIR__;

    /**
     * @var array
     */
    protected $configFiles = ['caffeinated.themes'];

    /**
     * @var array
     */
    protected $providers = [
        \Collective\Html\HtmlServiceProvider::class,
        \Caffeinated\Themes\Providers\ConsoleServiceProvider::class
    ];

    protected $provides = ['caffeinated.themes'];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = parent::register();

        $this->registerThemes();
        $this->registerViewFinder();

        $app->make('events')->listen('creating: *', function (\Illuminate\Contracts\View\View $view) use ($app)
        {
            $app->make('caffeinated.themes')->boot();
        });
    }

    /**
     * registerThemes
     */
    protected function registerThemes()
    {
        $this->app->singleton('caffeinated.themes', function (Application $app)
        {
            $themeFactory = new ThemeFactory($app->make('files'), $app->make('events'), $app->make('url'));
            $themeFactory->setPaths(config('caffeinated.themes.paths'));
            $themeFactory->setThemeClass(config('caffeinated.themes.themeClass'));
            $themeFactory->setActive(config('caffeinated.themes.active'));
            $themeFactory->setDefault(config('caffeinated.themes.default'));

            return $themeFactory;
        });
        $this->app->alias('caffeinated.themes', 'Caffeinated\Themes\Contracts\ThemeFactory');
    }

    /**
     * registerViewFinder
     */
    protected function registerViewFinder()
    {
        /**
         * @var $oldViewFinder FileViewFinder
         */
        $oldViewFinder = $this->app[ 'view.finder' ];

        $this->app->bind('view.finder', function ($app) use ($oldViewFinder)
        {
            $paths = array_merge(
                $app[ 'config' ][ 'view.paths' ],
                $oldViewFinder->getPaths()
            );

            $themesViewFinder = new ThemeViewFinder($app[ 'files' ], $paths, $oldViewFinder->getExtensions());
            $themesViewFinder->setThemes($app[ 'caffeinated.themes' ]);
            $app[ 'caffeinated.themes' ]->setFinder($themesViewFinder);

            foreach ( $oldViewFinder->getPaths() as $location )
            {
                $themesViewFinder->addLocation($location);
            }

            foreach ( $oldViewFinder->getHints() as $namespace => $hints )
            {
                $themesViewFinder->addNamespace($namespace, $hints);
            }

            return $themesViewFinder;
        });

        $this->app[ 'view' ]->setFinder($this->app[ 'view.finder' ]);
    }

}
