<?php
/**
* Part of the Caffeinated PHP packages.
*
* MIT License and copyright information bundled with this package in the LICENSE file
 */

namespace Caffeinated\Themes\Console;

use Laradic\Console\Command;

/**
 * This is the ThemePublishersCommand.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemePublishersCommand extends Command
{

    protected $name = 'themes:publishers';

    protected $description = 'List all available publishers.';

    public function fire()
    {
        $publishers = array_keys(app('themes')->getPublishers());
        $this->comment('Available publishers:');
        foreach ( $publishers as $publisher )
        {
            $this->line($publisher);
        }
    }
}
