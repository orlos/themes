<?php
/**
 * Part of the Robin Radic's PHP packages.
 *
 * MIT License and copyright information bundled with this package
 * in the LICENSE file or visit http://radic.mit-license.com
 */
namespace Caffeinated\Themes;

use Illuminate\Foundation\Application;
use Illuminate\View\FileViewFinder;
use Laradic\Support\ServiceProvider;

/**
 * This is the ThemeServiceProvider class.
 *
 * @package        Caffeinated\Themes
 * @version        1.0.0
 * @author         Robin Radic
 * @license        MIT License
 * @copyright      2015, Robin Radic
 * @link           https://github.com/robinradic
 */
class ThemeServiceProvider extends ServiceProvider
{
    protected $providers = [
        \Caffeinated\Themes\Providers\BusServiceProvider::class,
        \Caffeinated\Themes\Providers\EventServiceProvider::class,
      #  \Caffeinated\Themes\Providers\ConsoleServiceProvider::class,
        \Collective\Html\HtmlServiceProvider::class
    ];


    public function boot()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = parent::boot();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = parent::register();

        $this->linkConfig();

        $this->registerThemes();
        $this->registerViewFinder();

        $app->make('events')->listen('creating: *', function (\Illuminate\Contracts\View\View $view) use ($app)
        {
            $app->make('themes')->boot();
        });

    }

    protected function linkConfig()
    {
        $configPath = realpath(__DIR__ . '/../resources/config/config.php');
        $this->mergeConfigFrom($configPath, 'caffeinated.themes');
        $this->publishes([ $configPath => config_path('caffeinated.themes.php') ], 'config');
    }

    protected function registerThemes()
    {
        $this->app->singleton('themes', function (Application $app)
        {
            $themeFactory = new ThemeFactory($app->make('files'), $app->make('events'), $app->make('url'));
            $themeFactory->setPaths(config('caffeinated.themes.paths'));
            $themeFactory->setThemeClass(config('caffeinated.themes.themeClass'));
            $themeFactory->setActive(config('caffeinated.themes.active'));
            $themeFactory->setDefault(config('caffeinated.themes.default'));
            return $themeFactory;
        });
        $this->app->alias('themes', 'Caffeinated\Themes\Contracts\ThemeFactory');
    }


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
            $themesViewFinder->setThemes($app[ 'themes' ]);
            $app[ 'themes' ]->setFinder($themesViewFinder);

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
