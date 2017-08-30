<?php

namespace ATS\Bundle\ScheduleBundle\Cache;

use ATS\Bundle\ScheduleBundle\Util\Helper\CacheHelper;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Warms the HttpCache so that it doesn't need to be built on the first visit.
 * 
 * @author Austin Shinpaugh <asinpaugh@ou.edu>
 */
class HttpCacheWarmer implements CacheWarmerInterface
{
    protected $helper;
    
    /**
     * HttpCacheWarmer constructor.
     *
     * @param CacheHelper $helper
     */
    public function __construct(CacheHelper $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->helper
            ->warmHttpCache(true)
        ;
    }
}
