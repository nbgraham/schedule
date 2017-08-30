<?php

namespace ATS\Bundle\ScheduleBundle\Cache;

use ATS\Bundle\ScheduleBundle\Util\Helper\CommandHelper;
use Buzz\Browser;
use Buzz\Client\Curl;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Warms the HttpCache so that it doesn't need to be built on the first visit.
 * 
 * @author Austin Shinpaugh <asinpaugh@ou.edu>
 */
class HttpCacheWarmer implements CacheWarmerInterface
{
    private $helper;
    private $dev_domain;
    private $prod_domain;
    private $environment;
    
    /**
     * HttpCacheWarmer constructor.
     *
     * @param CommandHelper $helper
     * @param String        $dev_domain
     * @param String        $prod_domain
     * @param String        $env
     */
    public function __construct(CommandHelper $helper, $dev_domain, $prod_domain, $env)
    {
        $this->helper      = $helper;
        $this->dev_domain  = $dev_domain;
        $this->prod_domain = $prod_domain;
        $this->environment = $env;
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
        $this
            ->dumpAssets()
            ->makeRequest()
        ;
    }
    
    /**
     * Dumps the assets before generating the HttpCache.
     * This is required since some assets are loaded inline.
     * 
     * @return $this
     * @throws \ErrorException
     */
    protected function dumpAssets()
    {
        if (!$this->helper->prepareAssets($this->environment)) {
            throw new \ErrorException("Unable to generate {$this->environment}'s assets.");
        }
        
        return $this;
    }
    
    /**
     * Makes an Http request to build the cache.
     * 
     * @return $this
     */
    protected function makeRequest()
    {
        $browser = new Browser(new Curl());
        $urls    = [$this->prod_domain, $this->dev_domain];
        
        if ('dev' === $this->environment) {
            $urls = [$this->dev_domain, $this->prod_domain];
        }
        
        $browser->getClient()->setTimeout(30);
        
        foreach ($urls as $url) {
            $browser->get($url);
        }
        
        return $this;
    }
}
