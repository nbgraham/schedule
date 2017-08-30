<?php

namespace ATS\Bundle\ScheduleBundle\Util\Helper;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Helper class for commands.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class CommandHelper
{
    protected $root_dir;
    protected $env;
    
    public function __construct($root_dir, $env)
    {
        $this->root_dir = $root_dir;
        $this->env      = $env;
    }
    
    /**
     * Return the full path to the bin/console component.
     * 
     * @return string
     */
    public function getConsolePath()
    {
        return '/usr/local/bin/php '
            . $this->getAppRoot()
            . 'bin/console'
        ;
    }
    
    /**
     * Return the app root.
     * 
     * @return string
     */
    public function getAppRoot()
    {
        return $this->root_dir . '/../';
    }
    
    public function prepareAssets($env = null)
    {
        if (null === $env) {
            $env = $this->env;
        }
        
        $process = new Process($this->getConsolePath() . " assetic:dump --env={$env} --no-debug --force");
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        
        $finder = Finder::create()
            ->files()
            ->in($this->root_dir . '/../web/assets/compiled')
            ->name('controllers.js')
            ->name('libraries.js')
            ->name('utils.js')
            
            
            ->name('app.css')
            ->name('app_print.css')
            ->name('inline-libraries.css')
            ->name('libraries.css')
        ;
        
        return 7 === $finder->count();
    }
}
