<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base controller class for all controllers in the bundle.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
abstract class AbstractController extends FOSRestController
{
    public function getRepo($className)
    {
        return $this->getDoctrine()->getRepository($className);
    }
}