<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Providers;

use Caffeinated\Beverage\ConsoleServiceProvider as BaseConsoleProvider;

/**
 * This is the ConsoleServiceProvider.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ConsoleServiceProvider extends BaseConsoleProvider
{

    /**
     * The namespace where the commands are
     *
     * @var string
     */
    protected $namespace = 'Caffeinated\\Themes\\Console';

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'init' => 'ThemeInit',
        'make' => 'ThemeMake',
        'publish' => 'ThemePublish',
        'publishers' => 'ThemePublishers'
    ];

    /**
     * @var string
     */
    protected $prefix = 'caffeinated.themes.';
}
