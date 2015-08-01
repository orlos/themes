<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Traits;

/**
 * This is the ThemeProviderTrait.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
trait ThemeProviderTrait
{

    /**
     * addPackagePublisher
     *
     * @param $package
     * @param $path
     */
    protected function addPackagePublisher($package, $path)
    {
        app('themes')->addPackagePublisher($package, $path);
    }

    /**
     * addNamespacePublisher
     *
     * @param $namespace
     * @param $path
     */
    protected function addNamespacePublisher($namespace, $path)
    {
        app('themes')->addNamespacePublisher($namespace, $path);
    }

    protected function addThemePublisher($directory)
    {
        #app('themes')->getPath()
    }

}
