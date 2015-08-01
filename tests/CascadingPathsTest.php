<?php
/**
 * Part of the Robin Radic's PHP packages.
 *
 * MIT License and copyright information bundled with this package
 * in the LICENSE file or visit http://radic.mit-license.com
 */
namespace Caffeinated\Tests\Themes;

use Illuminate\Filesystem\Filesystem;
use Mockery as m;

/**
 * This is the ThemeTest.
 *
 * @package        Laradic\Tests
 * @version        1.0.0
 * @author         Robin Radic
 * @license        MIT License
 * @copyright      2015, Robin Radic
 * @link           https://github.com/robinradic
 */
class CascadingPathsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->instance('path.public', realpath(__DIR__ . '/fixture/public'));
        $this->app->register(\Caffeinated\Themes\ThemeServiceProvider::class);
    }

    protected function assertViewContent($view, $expected)
    {
        $content = $this->app[ 'view' ]->make($view)->render();
        $this->assertEquals($expected, $content);
    }

    protected function assertAssetContent($key, $expected)
    {
        $content = with(new Filesystem())->get($this->app[ 'caffeinated.themes' ]->assetPath($key));
        $this->assertEquals($expected, trim($content));
    }

    public function testDefinedThemes()
    {
        /**
         * @var \Caffeinated\Themes\ThemeFactory $themes
         */
        $themes = $this->app[ 'caffeinated.themes' ];

        /**
         * @var \Illuminate\View\Factory $view
         */
        $view = $this->app[ 'view' ];

        $this->assertEquals('frontend/example', $themes->getActive()->getSlug());
        $this->assertEquals('frontend/default', $themes->getDefault()->getSlug());
        $this->assertEquals('frontend/parent', $themes->getActive()->getParentTheme()->getSlug());
    }

    public function testViews()
    {

        /**
         * @var \Caffeinated\Themes\ThemeFactory $themes
         */
        $themes = $this->app[ 'caffeinated.themes' ];
        /**
         * @var \Illuminate\View\Factory $view
         */
        $view = $this->app[ 'view' ];


        $view->addNamespace('nstest', $themes->getPath('namespaces') . '/nstest');
        $this->assertViewContent('index', 'index of frontend/example');
        $this->assertViewContent('nstest::index', 'index of frontend/example::nstest');
        $this->assertViewContent('testvendor/testpkg::index', 'index of frontend/example::testvendor/testpkg');

        // test parent and default fallbacks
        $this->assertViewContent('parent-fallback', 'parent-fallback content');
        $this->assertViewContent('nstest::parent-fallback', 'nstest parent-fallback content');
        $this->assertViewContent('testvendor/testpkg::parent-fallback', 'testvendor/testpkg parent-fallback content');

        $this->assertViewContent('default-fallback', 'default-fallback content');
        $this->assertViewContent('nstest::default-fallback', 'nstest default-fallback content');
        $this->assertViewContent('testvendor/testpkg::default-fallback', 'testvendor/testpkg default-fallback content');
    }

    public function testAsset()
    {

        /**
         * @var \Caffeinated\Themes\ThemeFactory $themes
         */
        $themes = $this->app[ 'caffeinated.themes' ];
        /**
         * @var \Illuminate\View\Factory $view
         */
        $view = $this->app[ 'view' ];


        $view->addNamespace('nstest', $themes->getPath('namespaces') . '/nstest');
        $this->assertAssetContent('scriptfix.js', 'example');
        $this->assertAssetContent('nstest::scriptfix.js', 'scriptnstest');
        $this->assertAssetContent('testvendor/testpkg::scriptfix.js', 'testpkgscrit');

        $this->assertAssetContent('parent-fallback.js', 'parent-fallback content');
        $this->assertAssetContent('nstest::parent-fallback.js', 'nstest parent-fallback content');
        $this->assertAssetContent('testvendor/testpkg::parent-fallback.js', 'testvendor/testpkg parent-fallback content');

        $this->assertAssetContent('default-fallback.js', 'default-fallback content');
        $this->assertAssetContent('nstest::default-fallback.js', 'nstest default-fallback content');
        $this->assertAssetContent('testvendor/testpkg::default-fallback.js', 'testvendor/testpkg default-fallback content');

        #$themes->addNamespace('nstestaa', 'nstest');


        $this->assertEquals(url('themes/frontend/example/assets/scriptfix.js'), $themes->assetUrl('scriptfix.js'));


        #$this->assertViewContent('nstestaa::index', 'index of frontend/example::nstest');
    }


}
