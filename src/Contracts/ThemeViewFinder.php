<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Contracts;

/**
 * Interface ThemeViewFinder
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
interface ThemeViewFinder
{

    public function getHints();

    /**
     * Find the key
     *
     * @param $key
     * @return mixed
     */
    public function find($key);

    /** @return Factory */
    public function getThemes();

    /**
     * @param Factory $themes
     * @return $this
     */
    public function setThemes(Factory $themes);
}
