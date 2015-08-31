<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes;

use Caffeinated\Beverage\ServiceProvider;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\View\FileViewFinder;

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
    protected $configFiles = [ 'caffeinated.themes' ];

    /**
     * @var array
     */
    protected $providers = [
        \Collective\Html\HtmlServiceProvider::class,
        \Caffeinated\Themes\Providers\ConsoleServiceProvider::class
    ];

    protected $provides = [ 'caffeinated.themes' ];

    protected $singletons = [
        'caffeinated.themes' => Factory::class
    ];

    protected $aliases = [
        'caffeinated.themes' => Contracts\Factory::class
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = parent::register();
        $themeClass = $this->app->make('config')->get('caffeinated.themes.theme_class');
        $app->bind($themeClass, $themeClass);

        $this->registerViewFinder();

        $app->make('events')->listen('creating: *', function (\Illuminate\Contracts\View\View $view) use ($app) {
        
            $app->make('caffeinated.themes')->boot();
        });
    }

    /**
     * registerViewFinder
     */
    protected function registerViewFinder()
    {
        /**
         * @var $oldFinder FileViewFinder
         */
        $oldFinder = $this->app->make('view.finder');

        $this->app->bind('view.finder', function ($app) use ($oldFinder) {
        

            $paths = array_merge(
                $app[ 'config' ][ 'view.paths' ],
                $oldFinder->getPaths()
            );

            $themesViewFinder = new ThemeViewFinder($app[ 'files' ], $paths, $oldFinder->getExtensions());
            $themesViewFinder->setThemes($app[ 'caffeinated.themes' ]);
            $app[ 'caffeinated.themes' ]->setFinder($themesViewFinder);

            foreach ($oldFinder->getPaths() as $location) {
                $themesViewFinder->addLocation($location);
            }

            foreach ($oldFinder->getHints() as $namespace => $hints) {
                $themesViewFinder->addNamespace($namespace, $hints);
            }

            return $themesViewFinder;
        });

        $newFinder = $this->app->make('view.finder');
        $this->app->make('view')->setFinder($newFinder);
    }
}
