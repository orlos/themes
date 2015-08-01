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

use Laradic\Dev\Traits\FacadeTestCaseTrait;

/**
 * Class StrTest
 *
 * @package Laradic\Test\Support
 */
class ThemesFacadeTest extends TestCase
{
    use FacadeTestCaseTrait;

    public function setUp()
    {
        parent::setUp();
        $this->app->register(\Caffeinated\Themes\ThemeServiceProvider::class);
    }


    protected function getServiceProviderClass($app)
    {
        return \Caffeinated\Themes\ThemeServiceProvider::class;
    }

    /**
     * Get the facade accessor.
     *
     * @return string
     */
    protected function getFacadeAccessor()
    {
        return 'themes';
    }

    /**
     * Get the facade class.
     *
     * @return string
     */
    protected function getFacadeClass()
    {
        return \Caffeinated\Themes\Facades\Themes::class;
    }

    /**
     * Get the facade route.
     *
     * @return string
     */
    protected function getFacadeRoot()
    {
        return \Caffeinated\Themes\ThemeFactory::class;
    }
}
