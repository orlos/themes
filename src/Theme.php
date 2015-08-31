<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes;

use Caffeinated\Themes\Contracts\Factory as ThemeFactoryContract;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use vierbergenlars\SemVer\Internal\SemVer;

/**
 * This is the Theme.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class Theme
{
    /**
     * @var \Caffeinated\Themes\Factory
     */
    protected $themes;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $parentSlug;

    /**
     * @var Theme
     */
    protected $parentTheme;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var
     */
    protected $dispatcher;

    /**
     * @var bool Is booted
     */
    protected $booted = false;

    protected $container;

    /**
     * @param \Illuminate\Contracts\Container\Container  $container
     * @param \Caffeinated\Themes\Contracts\Factory      $themes
     * @param \Illuminate\Contracts\Events\Dispatcher    $dispatcher
     * @param                                            $path
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __construct(Container $container, ThemeFactoryContract $themes, Dispatcher $dispatcher, $path)
    {
        $this->container = $container;
        $this->themes     = $themes;
        $this->path       = $path;
        $this->dispatcher = $dispatcher;

        if (! $this->themes->getFiles()->exists($path . '/theme.php')) {
            throw new FileNotFoundException("Error while loading theme, could not find {$path}/theme.php");
        }

        $this->config = $this->themes->getFiles()->getRequire($path . '/theme.php');

        $this->name       = $this->config[ 'name' ];
        $this->slug       = $this->config[ 'slug' ];
        $this->parentSlug = $this->config[ 'parent' ];
        if (isset($this->parentSlug)) {
            $this->parentTheme = $this->themes->resolveTheme($this->parentSlug);
        }


        if (isset($this->config[ 'register' ]) && $this->config[ 'register' ] instanceof Closure) {
            $this->config[ 'register' ](app(), $this);
        }
    }

    /**
     * getCascadedPath
     *
     * @param string|null $cascadeType namespaces, packages or null
     * @param string|null $cascadeName the namespace, package or nulll
     * @param string|null $pathType    views, assets or null
     * @return string the path
     */
    public function getCascadedPath($cascadeType = null, $cascadeName = null, $pathType = null)
    {
        $path = $this->path;

        if (! is_null($cascadeType)) {
            $path .= '/' . $this->themes->getPath($cascadeType);
        }
        if (! is_null($cascadeName)) {
            $path .= '/' . $cascadeName;
        }

        if (! is_null($pathType)) {
            $path .= '/' . $this->themes->getPath($pathType);
        }

        return $path;
    }

    /**
     * getPath
     *
     * @param null $for
     * @return string
     */
    public function getPath($for = null)
    {
        if (is_null($for)) {
            return $this->path;
        } else {
            return $this->path . '/' . $this->themes->getPath($for);
        }
    }

    /**
     * boot
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->dispatcher->fire('booting theme: ', [ $this ]);

        if ($this->config('boot', false) && $this->config('boot') instanceof Closure) {
             call_user_func_array($this->config('boot'), [app(), $this]);
        }

        $this->booted = true;
    }

    /**
     * Get a config item using dot notation. The config is the theme.php array
     *
     * @param      $key
     * @param null $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return array_get($this->config, $key, $default);
    }

    /**
     * getConfig
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * getParentTheme
     *
     * @return \Caffeinated\Themes\Theme
     */
    public function getParentTheme()
    {
        return $this->parentTheme;
    }

    /**
     * getParentSlug
     *
     * @return array|mixed|string
     */
    public function getParentSlug()
    {
        return $this->parentSlug;
    }

    /**
     * getThemes
     *
     * @return \Caffeinated\Themes\Contracts\Factory|\Caffeinated\Themes\Factory
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * getName
     *
     * @return array|mixed|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * getSlug
     *
     * @return array|mixed|string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * hasParent
     *
     * @return bool
     */
    public function hasParent()
    {
        return isset($this->parentTheme);
    }

    /**
     * isActive
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->themes->getActive() instanceof $this;
    }

    /**
     * isDefault
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->themes->getDefault() instanceof $this;
    }

    /**
     * getSlugProvider
     *
     * @return mixed
     */
    public function getSlugProvider()
    {
        return explode('/', $this->slug)[ 0 ];
    }

    /**
     * getSlugKey
     *
     * @return mixed
     */
    public function getSlugKey()
    {
        return explode('/', $this->slug)[ 1 ];
    }

    /**
     * isBooted
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * getVersion
     *
     * @return \vierbergenlars\SemVer\Internal\SemVer
     */
    public function getVersion()
    {
        return new SemVer($this->config[ 'version' ]);
    }
}
