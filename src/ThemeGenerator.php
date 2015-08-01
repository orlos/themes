<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes;

use Illuminate\View\Compilers\BladeCompiler;
use Laradic\Support\Path;
use Laradic\Support\StubGenerator;

/**
 * This is the ThemeGenerator.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemeGenerator extends StubGenerator
{
    /** @var string Path to stubs */
    public $themeStubPath;

    /** @var string Path do dir */
    public $themeDirPath;

    /**
     * {@inheritDoc}
     */
    public function __construct(BladeCompiler $compiler)
    {
        parent::__construct($compiler);
        $this->themeStubPath = __DIR__ . '/../resources/stubs';
    }

    /**
     * generateTheme
     *
     * @param      $slug
     * @param      $name
     * @param null|string $parent
     * @param null|string $viewFile
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function generateTheme($slug, $name, $parent = null, $viewFile = null)
    {
        $path = Path::join($this->getThemeDirPath(), $slug);

        if ( $this->files->exists($path) )
        {
            return false;
        }

        $this->generateDirStruct($path);
        $themeStub        = $this->files->get(realpath(Path::join($this->themeStubPath, 'theme.php.stub')));
        $themeFileContent = "<?php \n" . $this->render($themeStub, compact('slug', 'name', 'parent'));
        $this->files->put(Path::join($path, 'theme.php'), $themeFileContent);
        if ( ! is_null($viewFile) )
        {
            $from = Path::join($this->themeStubPath, $viewFile);
            $to   = Path::join($path, config('laradic.themes.paths.views'), $viewFile);
            $this->files->copy($from, $to);
        }

        return true;
    }

    /**
     * getThemeDirPath
     *
     * @return mixed|string
     */
    protected function getThemeDirPath()
    {
        return isset($this->themeDirPath) ? $this->themeDirPath : head(config('laradic.themes.paths.themes'));
    }

    /**
     * generateDirStruct
     *
     * @param $path
     */
    protected function generateDirStruct($path)
    {
        $this->files->makeDirectory($path, 0775, true);
        $types = [ 'assets', 'namespaces', 'packages', 'views' ];

        foreach ( $types as $pathType )
        {
            $this->files->makeDirectory(Path::join($path, config('laradic.themes.paths.' . $pathType)), 0775, true);
        }
    }
}
