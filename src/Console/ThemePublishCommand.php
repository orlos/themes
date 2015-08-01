<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Console;

use Laradic\Console\Command;
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

    protected $name = 'themes:publish';

    protected $description = 'Publish ';

    public function fire()
    {
        $publisher = $this->argument('publisher');
        $theme     = $this->option('theme');

        app('themes')->publish($publisher, $theme);
        $this->info('Published ' . (! is_null($publisher) ? $publisher : 'all') . (! is_null($theme) ? " to theme $theme" : null));
    }

    public function getArguments()
    {
        return [
            [ 'publisher', InputArgument::OPTIONAL, 'The namespace or package to publish. If not provided, everything will be published. Check themes:publishers for available options' ]
        ];
    }

    public function getOptions()
    {
        return [
            [ 'theme', 't', InputOption::VALUE_OPTIONAL, 'The theme you want to publish to', null ]
        ];
    }
}
