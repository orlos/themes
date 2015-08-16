<?php
/**
 * Part of the Caffeinated PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Caffeinated\Themes\Console;

use Caffeinated\Beverage\Traits\NamespacedPackageTrait;
use Caffeinated\Themes\ThemeGenerator;
use Caffeinated\Beverage\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This is the ThemeMakeCommand.
 *
 * @package        Caffeinated\Themes
 * @author         Caffeinated Dev Team
 * @copyright      Copyright (c) 2015, Caffeinated
 * @license        https://tldrlegal.com/license/mit-license MIT License
 */
class ThemeMakeCommand extends Command
{

    use NamespacedPackageTrait;

    protected $signature = 'themes:make {slug: The slug of the theme}';

    protected $description = 'Publish ';

    /**
     * @var \Caffeinated\Beverage\Filesystem
     */
    protected $files;

    public function handle()
    {

        if ( ! $this->validateSlug($slug = $this->argument('slug')) )
        {
            return $this->error('Invalid slug');
        }

        $gen     = new ThemeGenerator(app('blade.compiler'));
        $success = $gen->generateTheme($slug, $slug . ' Theme');

        if ( ! $success )
        {
            return $this->error('theme already exists');
        }

        $this->info('Successfully created theme');
    }


}
