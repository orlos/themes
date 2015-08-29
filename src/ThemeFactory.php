<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes;

use ArrayAccess;
use ArrayIterator;
use Caffeinated\Beverage\Str;
use Caffeinated\Themes\Contracts\ThemeFactory as ThemeFactoryContract;
use Countable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\NamespacedItemResolver;
use IteratorAggregate;
use RuntimeException;

/**
 * This is the ThemeFactory.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemeFactory implements ArrayAccess, Countable, IteratorAggregate, ThemeFactoryContract
{

    /**
     * Contains all the resolved theme instances using slug => Class instance association
     *
     * @var Theme[]
     */
    protected $themes = [ ];

    /**
     * The active theme instance
     *
     * @var \Caffeinated\Themes\Theme
     */
    protected $active;

    /**
     * The default theme instance
     *
     * @var \Caffeinated\Themes\Theme
     */
    protected $default;

    /**
     * The filesystem object
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The theme package's view finder to locate theme views
     *
     * @var \Caffeinated\Themes\ThemeViewFinder
     */
    protected $finder;

    /**
     * The event dispatcher
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Filesystem paths to directories where themes are installed
     *
     * @var $paths array
     */
    protected $paths = [ ];

    /**
     * Collection of all theme publishers that have been added
     *
     * @var Publisher[]
     */
    protected $publishers = [ ];

    /** @var \Illuminate\Contracts\Routing\UrlGenerator */
    protected $url;

    /** @var string The theme class name */
    protected $themeClass;

    /**
     * Instantiates the class
     *
     * @param \Illuminate\Filesystem\Filesystem          $files
     * @param \Illuminate\Contracts\Events\Dispatcher    $events
     * @param \Illuminate\Contracts\Routing\UrlGenerator $url
     */
    public function __construct(Filesystem $files, Dispatcher $events, UrlGenerator $url)
    {
        $this->files      = $files;
        $this->dispatcher = $events;
        $this->url        = $url;
    }

    /**
     * Set the active theme that should be used
     *
     * @param string|\Caffeinated\Themes\Theme $theme The slug or Theme instance
     * @return $this
     */
    public function setActive($theme)
    {
        if (! $theme instanceof Theme) {
            $theme = $this->resolveTheme($theme);
        } else {
            if (! array_key_exists($theme->getSlug(), $this->themes)) {
                $this->themes[ $theme->getSlug() ] = $theme;
            }
        }

        $this->active = $theme;

        return $this;
    }

    /**
     * Get the activated theme
     *
     * @return \Caffeinated\Themes\Theme
     */
    public function getActive()
    {
        if (! isset($this->active)) {
            throw new RuntimeException('Could not get active theme because there isn\'t any defined');
        }

        return $this->active;
    }

    /**
     * Get the default theme
     *
     * @return \Caffeinated\Themes\Theme
     */
    public function getDefault()
    {
        if (! isset($this->default)) {
            return null;
        }

        return $this->default;
    }

    /**
     * Set the default theme
     *
     * @param string|\Caffeinated\Themes\Theme $theme The slug or Theme instance
     */
    public function setDefault($theme)
    {
        if (! $theme instanceof Theme) {
            $theme = $this->resolveTheme($theme);
        } else {
            if (! array_key_exists($theme->getSlug(), $this->themes)) {
                $this->themes[ $theme->getSlug() ] = $theme;
            }
        }
        $this->default = $theme;
    }

    /**
     * Resolve a theme using it's slug. It will check all theme paths for the required theme.
     * It will instantiate the theme, register it with the factory and return it.
     *
     * @param string $slug The theme slug
     * @return Theme
     */
    public function resolveTheme($slug)
    {
        if (array_key_exists($slug, $this->themes)) {
            return $this->themes[ $slug ];
        }

        list($area, $key) = with(new NamespacedItemResolver)->parseKey($slug);

        foreach ($this->paths[ 'themes' ] as $path) {
            $themePath = $this->getThemePath($path, $key, $area);

            if ($this->files->isDirectory($themePath)) {
                return $this->themes[ $slug ] = new $this->themeClass($this, $this->dispatcher, $themePath);
            }
        }
    }

    /**
     * Returns all resolved theme slugs
     *
     * @return array
     */
    public function all()
    {
        return array_keys($this->themes);
    }

    /**
     * Get a theme with the provided slug, equal to resolveTheme
     *
     * @param $slug
     * @return \Caffeinated\Themes\Theme
     */
    public function get($slug)
    {
        return $this->resolveTheme($slug);
    }

    /**
     * Check if a theme is present
     *
     * @param $slug
     * @return bool
     */
    public function has($slug)
    {
        $this->resolveTheme($slug);

        return in_array($slug, array_keys($this->themes), true);
    }

    /**
     * Get the number of themes
     *
     * @return int
     */
    public function count()
    {
        return count($this->themes);
    }

    /**
     * Add a namespace to the theme
     *
     * @param string $name
     * @param string $dirName
     * @return $this
     */
    public function addNamespace($name, $dirName)
    {
        $location = $this->getPath('namespaces') . '/' . $dirName;

        app('view')->addLocation($location);
        app('view')->addNamespace($name, $location);

        return $this;
    }

    /**
     * Get a path by type, as configured in config.
     *
     * @param string $type views, assets, namespaces or packages
     * @return string
     */
    public function getPath($type)
    {
        return $this->paths[ $type ];
    }

    /**
     * Get paths cascadingly for the defined options.
     *
     * @param string      $cascadeType The type, either namespaces, packages
     * @param null|string $cascadeName The namespaced or package name
     * @param null|string $pathType    The path type like views or assets
     * @param null|string $theme
     * @return array
     */
    public function getCascadedPaths($cascadeType, $cascadeName = null, $pathType = null, $theme = null)
    {
        $paths  = array();
        $looped = array();

        $current = is_null($theme) ? $this->getActive() : $this->resolveTheme($theme);

        while (true) {
            $paths[]  = $current->getCascadedPath($cascadeType, $cascadeName, $pathType);
            $looped[] = $current;

            if (! $parent = $current->getParentTheme()) {
                break;
            }

            if ($parent === $this->getDefault()) {
                break;
            }

            $current = $parent;
        }

        $default = $this->getDefault();
        if (! in_array($default, $looped, true)) {
            $paths[] = $default->getCascadedPath($cascadeType, $cascadeName, $pathType);
        }

        return $paths;
    }

    /**
     * Get the path tot the theme
     *
     * @param      $path
     * @param      $key
     * @param null $area
     * @return string
     */
    public function getThemePath($path, $key, $area = null)
    {
        $split = '/(\/|\\\)/';

        if (($keyCount = count(preg_split($split, $key))) > 2) {
            throw new RuntimeException("Theme had folder depth of [{$keyCount}] however it must be less than or equal to [2].");
        }

        if (isset($area)) {
            if (($areaCount = count(preg_split($split, $area))) != 1) {
                throw new RuntimeException("Theme area had folder depth of [{$areaCount}] however it must match [1].");
            }

            return "{$path}/{$area}/{$key}";
        }

        return "{$path}/{$key}";
    }

    /**
     * Register/add a theme publisher that publishes as a package
     *
     * @param string      $package    Package name
     * @param string      $sourcePath Path to the theme
     * @param string|null $theme      Exclude to a specific theme using tthis slug
     * @return $this
     */
    public function addPackagePublisher($package, $sourcePath, $theme = null)
    {
        $theme = is_null($theme) ? $this->getActive() : $this->resolveTheme($theme);

        $this->publishers[ $package ] = Publisher::create($this->getFiles())
            ->asPackage($package)
            ->from($sourcePath)
            ->toTheme($theme);

        return $this;
    }

    /**
     * Register/add a theme publisher that publishes as a namespace
     *
     * @param string      $namespace  Name
     * @param string      $sourcePath Path to the theme
     * @param string|null $theme      Exclude to a specific theme using tthis slug
     * @return $this
     */
    public function addNamespacePublisher($namespace, $sourcePath, $theme = null)
    {
        $theme = is_null($theme) ? $this->getActive() : $this->resolveTheme($theme);

        $this->publishers[ $namespace ] = Publisher::create($this->getFiles())
            ->asNamespace($namespace)
            ->from($sourcePath)
            ->toTheme($theme);

        return $this;
    }

    /**
     * Publish an namespace or package
     *
     * @param null $namespaceOrPackage
     * @param null $theme
     */
    public function publish($namespaceOrPackage = null, $theme = null)
    {
        if (is_null($namespaceOrPackage)) {
            foreach ($this->publishers as $publisher) {
                if (! is_null($theme)) {
                    $publisher->toTheme($theme instanceof Theme ? $theme : $this->resolveTheme($theme));
                }
                $publisher->publish();
            }
        } else {
            if (isset($this->publishers[ $namespaceOrPackage ])) {
                if (! is_null($theme)) {
                    $this->publishers[ $namespaceOrPackage ]->toTheme($theme instanceof Theme ? $theme : $this->resolveTheme($theme));
                }
                $this->publishers[ $namespaceOrPackage ]->publish();
            } else {
                throw new \InvalidArgumentException("Could not publish [$namespaceOrPackage]. The publisher could not be resolved for $namespaceOrPackage");
            }
        }
    }

    /**
     * Get the value of publishers
     *
     * @return Publisher[]
     */
    public function getPublishers()
    {
        return $this->publishers;
    }

    /**
     * getPath
     *
     * @param null $key
     * @return string
     */
    public function assetPath($key = null)
    {
        list($section, $relativePath, $extension) = with(new NamespacedItemResolver)->parseKey($key);

        if ($key === null) {
            return $this->getActive()->getPath('assets');
        }

        if ($relativePath === null || strlen($relativePath) === 0) {
            if (array_key_exists($section, $this->finder->getHints())) {
                return $this->getActive()->getCascadedPath('namespaces', $section, 'assets');
            }

            return $this->getActive()->getCascadedPath('packages', $section, 'assets');
        }

        if (isset($section)) {
            if (array_key_exists($section, $this->finder->getHints())) {
                $paths = $this->getCascadedPaths('namespaces', $section, 'assets');
            } else {
                $paths = $this->getCascadedPaths('packages', $section, 'assets');
            }
        } else {
            $paths = $this->getCascadedPaths(null, null, 'assets');
        }

        $file = null;

        foreach ($paths as $path) {
            $file = rtrim($path, '/') . '/' . $relativePath . '.' . $extension;

            if ($this->files->exists($file)) {
                return $file;
            }
        }

        return $file;
    }

    /**
     * assetUrl
     *
     * @param null $key
     * @return string
     */
    public function assetUrl($key = null)
    {
        return $this->url->to($this->assetUri($key));
    }

    /**
     * assetUri
     *
     * @param null $key
     * @return string
     */
    public function assetUri($key = null)
    {
        $path = Str::create($this->assetPath($key));
        if ($path->startsWith(public_path())) {
            $path = $path->removeLeft(public_path() . '/');
            if ($path->endsWith('.')) {
                $path = $path->removeRight('.');
            }
        }

        return (string)$path;
    }

    /**
     * Boot the active theme
     *
     * @param bool $bootParent
     * @param bool $bootDefault
     */
    public function boot($bootParent = true, $bootDefault = false)
    {
        $this->getActive()->boot();
        if ($bootParent && $this->getActive()->hasParent()) {
            $this->getActive()->getParentTheme()->boot();
        }
        if ($bootDefault) {
            $this->getDefault()->boot();
        }
    }

    /**
     * Get the theme view finder instance
     *
     * @return \Caffeinated\Themes\ThemeViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * setFinder
     *
     * @param \Caffeinated\Themes\ThemeViewFinder $finder
     * @return $this
     */
    public function setFinder(ThemeViewFinder $finder)
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * Get the filesystem object
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the filesystem object
     *
     * @param $files
     * @return $this
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * offsetExists
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->themes);
    }

    /**
     * offsetGet
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->themes[ $key ];
    }

    /**
     * offsetSet
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->themes[] = $value;
        } else {
            $this->themes[ $key ] = $value;
        }
    }

    /**
     * offsetUnset
     *
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        unset($this->themes[ $key ]);
    }

    /**
     * getIterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->themes);
    }

    /**
     * get themeClass value
     *
     * @return mixed
     */
    public function getThemeClass()
    {
        return $this->themeClass;
    }

    /**
     * Set the themeClass value
     *
     * @param mixed $themeClass
     * @return ThemeFactory
     */
    public function setThemeClass($themeClass)
    {
        $this->themeClass = $themeClass;

        return $this;
    }

    /**
     * Set the paths value
     *
     * @param array $paths
     * @return ThemeFactory
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;

        return $this;
    }
}
