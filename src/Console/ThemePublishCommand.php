<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Console;

use Caffeinated\Beverage\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * This is the ThemePublishCommand.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemePublishCommand extends Command
{

    protected $signature = 'themes:publish
                            {publisher: The namespace or package to publish. If not provided, everything will be published. Check themes:publishers for available options}
                            {--theme=null: The theme you want to publish to}';

    protected $description = 'Publish ';

    public function handle()
    {
        $publisher = $this->argument('publisher');
        $theme     = $this->option('theme');

        app('themes')->publish($publisher, $theme);
        $this->info('Published ' . (! is_null($publisher) ? $publisher : 'all') . (! is_null($theme) ? " to theme $theme" : null));
    }

}
