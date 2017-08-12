<?php

namespace ATS\Bundle\ScheduleBundle\Twig;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScheduleExtension extends \Twig_Extension
{
    protected $root_dir;
    
    public function __construct($root)
    {
        $this->root_dir = $root;
    }
    
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('inline_resource', [$this, 'renderResource'])
        ];
    }
    
    public function renderResource($path)
    {
        $path     = '/' === substr($path, 0, 1) ? $path : '/' . $path;
        $filepath = $this->root_dir . '/../web' . $path;
        $file     = new \SplFileObject($filepath);
        
        return $file->fread($file->getSize());
    }
}