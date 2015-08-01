<?php


namespace Caffeinated\Tests\Themes;

use Laradic\Dev\AbstractTestCase;
use Mockery as m;

/**
 * Class ViewTest
 *
 * @author     Robin Radic
 * @inheritDoc
 */
abstract class TestCase extends AbstractTestCase
{
    protected $paths;

    /** @inheritdoc */
    public function setUp()
    {
        parent::setUp();

        $this->paths = [
            'themes'     => array(
                public_path('themes'),
                public_path()
            ),
            'namespaces' => 'namespaces',
            'packages'   => 'packages',
            'views'      => 'views',
            'assets'     => 'assets',
            'cache'      => public_path('cache')
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

        $config = $app->make('config');
        $config->set('app.key', 'sG7qHHCc0jAseXbQx5BEv8DiZn4x7p4C');
        parent::getEnvironmentSetUp($app);
    }

    protected function _getThemeConfig(array $config = [ ])
    {
        return array_replace_recursive([
            'parent'  => null,
            'name'    => 'Frontend example',
            'slug'    => 'frontend/example',
            'version' => '0.0.1',
        ], $config);
    }

    protected function assertTheme($theme)
    {
        $this->assertInstanceOf(\Caffeinated\Themes\Theme::class, $theme);
        $this->assertInstanceOf(\vierbergenlars\SemVer\Internal\SemVer::class, $theme->getVersion());
    }

    /**
     * Tear down the test case.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }
}
