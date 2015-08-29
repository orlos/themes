<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes;

use Caffeinated\Themes\Contracts\ThemeFactory as ThemeFactoryContract;
use Caffeinated\Themes\Contracts\ThemeViewFinder as ThemeViewFinderContract;
use Illuminate\Support\NamespacedItemResolver;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

/**
 * This is the ThemeViewFinder.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemeViewFinder extends FileViewFinder implements ThemeViewFinderContract
{

    /**
     * @var $themes \Caffeinated\Themes\ThemeFactory
     */
    protected $themes;

    /** @inheritdoc */
    public function find($name)
    {
        $name = str_replace('.', '/', $name);

        if (! isset($this->themes)) {
            return parent::find($name);
        }

        $resolver = new NamespacedItemResolver;
        list($area, $view) = $resolver->parseKey($name);

        try {
            if (isset($area)) {
                if (isset($this->hints[ $area ])) {
                    $sectionType = 'namespaces';
                    $paths       = $this->themes->getCascadedPaths('namespaces', $area, 'views');
                } else {
                    $sectionType = 'packages';
                    $paths       = $this->themes->getCascadedPaths('packages', $area, 'views');
                }

                $view = $this->findInPaths($view, $paths);
            } else {
                $paths = $this->themes->getCascadedPaths('views');
                $view  = $this->findInPaths($view, $paths);
            }
        } // We couldn't find the view using our theming system
        catch (InvalidArgumentException $e) {
            try {
                return parent::find($name);
            } catch (InvalidArgumentException $e) {
                $active = $this->themes->getActive();

                if (isset($area)) {
                    $message = sprintf('Could not find view [%s] in [%s] of [%s]', $name, $sectionType, $active->getSlug());
                } else {
                    $message = sprintf('View [%s] could not be found in [%s]', $name, $active->getSlug());
                }

                throw new InvalidArgumentException($message);
            }
        }

        return $view;
    }

    /** @inheritdoc */
    public function getThemes()
    {
        return $this->themes;
    }

    /** @inheritdoc */
    public function setThemes(ThemeFactoryContract $themes)
    {
        $this->themes = $themes;

        return $this;
    }
}
