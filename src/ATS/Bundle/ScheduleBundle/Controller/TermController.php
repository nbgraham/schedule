<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;

/**
 * Term controller.
 * 
 * @RouteResource("/term", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class TermController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetch a collection of terms and term blocks.
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function cgetAction()
    {
        $terms = $this->getRepo('ATSScheduleBundle:Term')
            ->findAll()
        ;
        
        return ['terms' => $terms];
    }
}
