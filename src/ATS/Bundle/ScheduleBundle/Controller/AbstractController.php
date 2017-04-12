<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

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
    public function getRepo($className)
    {
        return $this->getDoctrine()->getRepository($className);
    }
}