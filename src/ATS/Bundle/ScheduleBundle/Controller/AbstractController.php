<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Base controller class for all controllers in the bundle.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
abstract class AbstractController extends FOSRestController
{
    /**
     * @param $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepo($className)
    {
        return $this->getDoctrine()->getRepository($className);
    }
    
    /**
     * Fetch the most recent update log.
     * 
     * @return UpdateLog
     */
    protected function getLastUpdateLog()
    {
        $repo   = $this->getDoctrine()->getRepository(UpdateLog::class);
        $update = $repo->findBy([], ['start' => 'DESC'], 1);
        
        return current($update);
    }
}