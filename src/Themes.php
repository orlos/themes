<?php

namespace Caffeinated\Themes;

use Caffeinated\Themes\Exceptions\FileMissingException;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Illuminate\View\Factory as ViewFactory;
use URL;
use File;

/* orlos&eleiva version */

class Themes
{
    /**
     * @var string
     */
    protected $active;

    /**
     * @var array
     */
    protected $components;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var View
     */
    protected $viewFactory;

    /**
     * Constructor method.
     *
     * @param Filesystem $files
     * @param Repository $config
     * @param ViewFactory $viewFactory
     */
    public function __construct(Filesystem $files, Repository $config, ViewFactory $viewFactory)
    {
        $this->config = $config;
        $this->files = $files;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Register custom namespaces for all themes.
     *
     * @return null
     */
    public function register()
    {
        foreach ($this->all() as $theme) {
            $this->registerNamespace($theme);
        }
    }

    /**
     * Register custom namespaces for specified theme.
     *
     * @param string $theme
     * @return null
     */
    public function registerNamespace($theme)
    {
        $this->viewFactory->addNamespace($theme, $this->getThemePath($theme) . 'views');
    }

    /**
     * Get all themes.
     *
     * @return Collection
     */
    public function all()
    {
        $themes = [];

        if ($this->files->exists($this->getPath())) {
            $scannedThemes = $this->files->directories($this->getPath());

            foreach ($scannedThemes as $theme) {
                $themes[] = basename($theme);
            }
        }

        return new Collection($themes);
    }

    /**
     * Check if given theme exists.
     *
     * @param  string $theme
     * @return bool
     */
    public function exists($theme)
    {
        return in_array($theme, $this->all()->toArray());
    }

    /**
     * Gets themes path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path ?: $this->config->get('themes.paths.absolute');
    }

    /**
     * Sets themes path.
     *
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Gets active theme.
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active ?: $this->config->get('themes.active');
    }

    /**
     * Sets active theme.
     *
     * @return Themes
     */
    public function setActive($theme)
    {
        $this->active = $theme;

        return $this;
    }

    /**
     * Get theme layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Sets theme layout.
     *
     * @return Themes
     */
    public function setLayout($layout)
    {
        $this->layout = $this->getView($layout);

        return $this;
    }

    /**
     * Gets the given view file.
     *
     * @param  string $view
     * @return string|null
     */
    public function getView($view)
    {
        $activeTheme = $this->getActive();
        $parent = $this->getProperty($activeTheme . '::parent');

        $views = [
            'theme' => $this->getThemeNamespace($view),
            'parent' => $this->getThemeNamespace($view, $parent),
            'child' => $this->config->get('child.slug'),
            'module' => $this->getModuleView($view),
            'base' => $view
        ];

        foreach ($views as $view) {
            if ($this->viewFactory->exists($view)) {
                return $view;
            }
        }

        return false;
    }

    /**
     * Render theme view file.
     *
     * @param string $view
     * @param array $data
     * @return View
     */
    public function view($view, $data = array())
    {
        $this->autoloadComponents($this->getActive());
        $this->addBonsai($this->getActive());

        if (!is_null($this->layout)) {
            $data['theme_layout'] = $this->getLayout();
        }


        $viewToDisplay = $this->getChildViewIfExists($view);
        if(false == $viewToDisplay) {
            $viewToDisplay = $this->getView($view);
        }

        return $this->viewFactory->make($this->getView($viewToDisplay), $data);
    }

    /**
     * Checks if the given view file exists (anywhere).
     *
     * @param  string $view
     * @return bool
     */
    public function viewExists($view)
    {
        return ($this->getView($view)) ? true : false;
    }

    /**
     * Return a new theme view response from the application.
     *
     * @param  string $view
     * @param  array $data
     * @param  int $status
     * @param  array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response($view, $data = array(), $status = 200, array $headers = array())
    {
        return new Response($this->view($view, $data), $status, $headers);
    }

    /**
     * Gets the specified themes path.
     *
     * @param string $theme
     * @return string
     */
    public function getThemePath($theme)
    {
        return $this->getPath() . "/{$theme}/";
    }

    /**
     * Get path of theme JSON file.
     *
     * @param  string $theme
     * @return string
     */
    public function getJsonPath($theme)
    {
        return $this->getThemePath($theme) . '/theme.json';
    }

    /**
     * Get theme JSON content as an array.
     *
     * @param  string $theme
     * @return array|mixed
     */
    public function getJsonContents($theme)
    {
        $theme = strtolower($theme);

        $default = [];

        if (!$this->exists($theme))
            return $default;

        $path = $this->getJsonPath($theme);

        if ($this->files->exists($path)) {
            $contents = $this->files->get($path);

            return json_decode($contents, true);
        } else {
            $message = "Theme [{$theme}] must have a valid theme.json manifest file.";

            throw new FileMissingException($message);
        }
    }

    /**
     * Set theme manifest JSON content property value.
     *
     * @param  string $theme
     * @param  array $content
     * @return integer
     */
    public function setJsonContents($theme, array $content)
    {
        $content = json_encode($content, JSON_PRETTY_PRINT);

        return $this->files->put($this->getJsonPath($theme), $content);
    }

    /**
     * Get a theme manifest property value.
     *
     * @param  string $property
     * @param  null|string $default
     * @return mixed
     */
    public function getProperty($property, $default = null)
    {
        list($theme, $key) = explode('::', $property);

        return array_get($this->getJsonContents($theme), $key, $default);
    }

    /**
     * Set a theme manifest property value.
     *
     * @param  string $property
     * @param  mixed $value
     * @return bool
     */
    public function setProperty($property, $value)
    {
        list($theme, $key) = explode('::', $property);

        $content = $this->getJsonContents($theme);

        if (count($content)) {
            if (isset($content[$key])) {
                unset($content[$key]);
            }

            $content[$key] = $value;

            $this->setJsonContents($theme, $content);

            return true;
        }

        return false;
    }

    /**
     * Generate a HTML link to the given asset using HTTP for the
     * currently active theme.
     *
     * @return string
     */
    public function asset($asset)
    {
        $segments = explode('::', $asset);
        $theme = null;

        if (count($segments) == 2) {
            list($theme, $asset) = $segments;
        } else {
            $asset = $segments[0];
        }


        $urlPath = $this->config->get('themes.paths.base').'/'
            .($theme ?: $this->getActive()).'/views/'
            .$this->config->get('child.location').'/'
            .$this->config->get('child.slug').'/'
            .$this->config->get('themes.paths.assets').'/'
            .$asset;

        if(File::exists($urlPath)) {

            return url($urlPath);

        } else {
            return url($this->config->get('themes.paths.base').'/'
                .($theme ?: $this->getActive()).'/'
                .$this->config->get('themes.paths.assets').'/'
                .$asset);
        }

    }

    public function trans($key, $locale = 'en', $substitute = [])
    {
        $segments = explode('::', $key);
        $theme = null;
        $messages = [];

        $locale = session('tenantLocale') ? session('tenantLocale') : ($locale != null ? $locale : 'en');

        list($theme, $key) = $segments;

        $messageLocation = "views" . DIRECTORY_SEPARATOR . $this->config->get('child.location'). DIRECTORY_SEPARATOR . $this->config->get('child.slug') . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "messages.php";
        $childPath = $this->getThemePath($theme) . $messageLocation;
        $childTransExists = false;

        if(File::exists($childPath)) {
            $messages = include $childPath;
            if(isset($messages[$key])) {
                $childTransExists = true;

            }
        }

        if(false === $childTransExists) {

            $messageLocation = "resources" . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . "messages.php";
            $path = $this->getThemePath($theme) . $messageLocation;
            $messages = include $path;

        }

        if (!empty($substitute)) {
            $translation = $messages[$key];
            foreach ($substitute as $k => $val) {
                $translation = str_replace('{' . $k . '}', $val, $translation);
            }
        } else {
            $translation = isset($messages[$key]) ? $messages[$key] : $key;
        }
        return $translation;
    }

    /**
     * Generate a HTML link to the given asset using HTTPS for the
     * currently active theme.
     *
     * @return string
     */
    public function secureAsset($asset)
    {
        return preg_replace("/^http:/i", "https:", $this->asset($asset));
    }

    /**
     * Get the specified themes View namespace.
     *
     * @param string $key
     * @return string
     */
    protected function getThemeNamespace($key, $theme = null)
    {
        if (is_null($theme)) {
            return $this->getActive() . "::{$key}";
        } else {
            return $theme . "::{$key}";
        }
    }

    /**
     * Autoload a themes compontents file.
     *
     * @param  string $theme
     * @return null
     */
    protected function autoloadComponents($theme)
    {
        $activeTheme = $this->getActive();
        $path = $this->getPath();
        $parent = $this->getProperty($activeTheme . '::parent');
        $themePath = $path . '/' . $theme;
        $componentsFilePath = $themePath . '/components.php';

        if (!empty($parent)) {
            $parentPath = $path . '/' . $parent;
            $parentComponentsFilePath = $parentPath . '/components.php';

            if (file_exists($parentPath)) {
                include($parentComponentsFilePath);
            }
        }

        if (file_exists($componentsFilePath)) {
            include($componentsFilePath);
        }
    }

    /**
     * Add bonsai.json file is the Caffeinated Bonsai package
     * is present in the application.
     *
     * @param  string $theme
     * @return null
     */
    protected function addBonsai($theme)
    {
        if (class_exists('Caffeinated\Bonsai\Bonsai')) {
            $activeTheme = $this->getActive();
            $path = $this->getPath();
            $parent = $this->getProperty($activeTheme . '::parent');
            $themePath = $path . '/' . $theme;
            $bonsaiPath = $themePath . '/bonsai.json';

            if (!empty($parent)) {
                $parentPath = $path . '/' . $parent;
                $parentBonsaiPath = $parentPath . '/bonsai.json';

                if (file_exists($parentBonsaiPath)) {
                    \Bonsai::add($parentBonsaiPath);
                }
            }

            if (file_exists($bonsaiPath)) {
                \Bonsai::add($bonsaiPath);
            }
        }
    }

    /**
     * Get module view file.
     *
     * @param  string $view
     * @return null|string
     */
    protected function getModuleView($view)
    {
        if (class_exists('Caffeinated\Modules\Modules')) {
            $viewSegments = explode('.', $view);

            if ($viewSegments[0] == 'modules') {
                $module = $viewSegments[1];
                $view = implode('.', array_slice($viewSegments, 2));

                return "{$module}::{$view}";
            }
        }

        return null;
    }

    public function getChildViewIfExists($view) {
        $childRoute = $this->config->get('child.location');
        $childSlug = $this->config->get('child.slug');

        if(!empty($childRoute) and !empty($childSlug )) {

            $activeTheme = $this->getActive();
            $subTheme = $activeTheme . '::' . $childRoute . '.' .$childSlug. '.' . $view;

            if($this->viewFactory->exists($subTheme)) {
                return $subTheme;
            }
        }


        return false;
    }



}
