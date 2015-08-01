<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Console;

use Caffeinated\Themes\ThemeGenerator;
use Laradic\Console\Command;

/**
 * This is the ThemeInitCommand.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemeInitCommand extends Command
{

    protected $name = 'themes:init';

    protected $description = 'Create some initial theme files and dir structure. It will place it all in the first configured theme folder (laradic.themes.paths.themes) ';

    public function fire()
    {
        $themes = [
            [ 'example/default', 'Example Default Theme', null, 'layout.blade.php' ],
            [ 'example/main', 'Example Main Theme', null, 'index.blade.php' ],
            [ 'example/other', 'Example Other Theme', 'example/main', 'something.blade.php' ],
            [ 'another-example/admin', 'Another Example Admin Theme', null, 'admin.blade.php' ]
        ];

        $gen = new ThemeGenerator(app('blade.compiler'));

        foreach ( $themes as $theme )
        {
            $success = $gen->generateTheme($theme[ 0 ], $theme[ 1 ], $theme[ 2 ], $theme[ 3 ]);

            if ( ! $success )
            {
                $this->error('theme already exists');
            }
        }

        $this->info('Successfully created init themes');
    }

}
