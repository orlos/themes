<?php
/**
 * Part of the Robin Radic's PHP packages.
 *
 * MIT License and copyright information bundled with this package
 * in the LICENSE file or visit http://radic.mit-license.com
 */
namespace Caffeinated\Tests\Themes;

use Caffeinated\Themes\Publisher;
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
class PublisherTest extends TestCase
{
    /** @var \Mockery\MockInterface */
    protected $fs;

    /** @var \Mockery\MockInterface */
    protected $theme;

    /** @var \Caffeinated\Themes\Publisher */
    protected $publisher;

    public function setUp()
    {
        parent::setUp();
        $this->fs        = m::mock('Illuminate\Filesystem\Filesystem');
        $this->theme     = m::mock('Caffeinated\Themes\Theme');
        $this->publisher = new Publisher($this->fs);
        $this->publisher->toTheme($this->theme);
    }

    public function testPublishPackage()
    {
        $name        = 'testvendor/testpkg';
        $sourcePath  = __DIR__ . '/fixture/public/themes/frontend/default';
        $destination = 'public/themes/frontend/default/packages/testvendor/testpkg';
        $this->theme->shouldReceive('getCascadedPath')->once()->with(m::mustBe('packages'), m::mustBe($name))->andReturn($destination);
        $this->fs->shouldReceive('exists')->once()->with(m::mustBe($destination))->andReturn(true);
        $this->fs->shouldReceive('copyDirectory')->once()->with(m::mustBe($sourcePath), m::mustBe($destination))->andReturn();
        $this->publisher
            ->asPackage($name)
            ->from($sourcePath)
            ->publish();
    }

    public function testPublishNamespace()
    {
        $name        = 'nstest';
        $sourcePath  = __DIR__ . '/fixture/public/themes/frontend/default';
        $destination = 'public/themes/frontend/default/namespaces/' . $name;
        $this->theme->shouldReceive('getCascadedPath')->once()->with(m::mustBe('namespaces'), m::mustBe($name))->andReturn($destination);
        $this->fs->shouldReceive('exists')->once()->with(m::mustBe($destination))->andReturn(true);
        $this->fs->shouldReceive('copyDirectory')->once()->with(m::mustBe($sourcePath), m::mustBe($destination))->andReturn();
        $this->publisher
            ->asNamespace($name)
            ->from($sourcePath)
            ->publish();
    }

}
