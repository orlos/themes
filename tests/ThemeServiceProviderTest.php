<?php
/**
 * Part of the Laradic packages.
 * MIT License and copyright information bundled with this package in the LICENSE file.
 *
 * @author      Robin Radic
 * @license     MIT
 * @copyright   2011-2015, Robin Radic
 * @link        http://radic.mit-license.org
 */
namespace Caffeinated\Tests\Themes;

use Caffeinated\Dev\Testing\Traits\ServiceProviderTester;

/**
 * Class StrTest
 *
 * @package Laradic\Test\Support
 */
class ThemeSupportServiceProviderTest extends TestCase
{
    use ServiceProviderTester;

    protected function getServiceProviderClass($app)
    {
        return \Caffeinated\Themes\ThemeServiceProvider::class;
    }
}
