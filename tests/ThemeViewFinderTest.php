<?php
/**
 * Part of the Robin Radic's PHP packages.
 *
 * MIT License and copyright information bundled with this package
 * in the LICENSE file or visit http://radic.mit-license.com
 */
namespace Caffeinated\Tests\Themes;

use Caffeinated\Themes\ThemeViewFinder;
use Mockery as m;

/**
 * This is the ThemeViewFinderTest.
 *
 * @package        Laradic\Tests
 * @version        1.0.0
 * @author         Robin Radic
 * @license        MIT License
 * @copyright      2015, Robin Radic
 * @link           https://github.com/robinradic
 */
class ThemeViewFinderTest extends TestCase
{

    /** @var \Mockery\MockInterface */
    protected $tfMock;

    /** @var \Mockery\MockInterface */
    protected $fsMock;

    protected $hints;

    public function setUp()
    {
        parent::setUp();
        $this->tfMock = m::mock('\Caffeinated\Themes\ThemeFactory');

        $this->fsMock = m::mock('Illuminate\Filesystem\Filesystem');

        $this->hints = [ ];
    }

    /** Instantiates the class */
    public function testFindPackage()
    {
        $this->doTest(
            'laradic/testfinder::path.to.view',
            [ 'packages', 'laradic/testfinder', 'views' ],
            'themedir/vendor/name/packages/laradic/testfinder/views',
            'path/to/view.blade.php'
        );
    }

    /** Instantiates the class */
    public function testFindNamespace()
    {
        $this->hints[ 'thetestdir' ] = '';
        $this->doTest(
            'thetestdir::path.to.view',
            [ 'namespaces', 'thetestdir', 'views' ],
            'themedir/vendor/name/namespaces/thetestdir/views',
            'path/to/view.blade.php'
        );
    }

    /** Instantiates the class */
    public function testFind()
    {
        $this->doTest(
            'path.to.view',
            [ 'views' ],
            'themedir/vendor/name/views',
            'path/to/view.blade.php'
        );
    }

    protected function doTest($findStr, $withArgs, $dir, $filePath)
    {
        $tf = $this->tfMock->shouldReceive('getCascadedPaths')->once()
            ->withArgs($withArgs)->andReturn([ $dir ])->getMock();

        $fs = $this->fsMock->shouldReceive('exists')->once()
            ->with($dir . '/' . $filePath)->andReturn(true)->getMock();


        $finder   = $this->getThemeFinder($fs, $tf);
        $viewPath = $finder->find($findStr);
        $this->assertEquals($dir . '/' . $filePath, $viewPath);
    }

    protected function getThemeFinder($fsMock, $themeFactoryMock)
    {
        $app = $this->app;

        $oldViewFinder = $app[ 'view.finder' ];

        $paths = array_merge(
            $app[ 'config' ][ 'view.paths' ],
            $oldViewFinder->getPaths()
        );

        $themesViewFinder = new ThemeViewFinder($fsMock, $paths, $oldViewFinder->getExtensions());
        $themesViewFinder->setThemes($themeFactoryMock);

        foreach ($oldViewFinder->getPaths() as $location) {
            $themesViewFinder->addLocation($location);
        }

        foreach ($oldViewFinder->getHints() as $namespace => $hints) {
            $themesViewFinder->addNamespace($namespace, $hints);
        }

        foreach ($this->hints as $namespace => $hints) {
            $themesViewFinder->addNamespace($namespace, $hints);
        }

        return $themesViewFinder;
    }

    public function tearDown()
    {
        m::close();
    }
}
