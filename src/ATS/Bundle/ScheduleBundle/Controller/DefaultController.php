<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Homepage.
 * 
 * @Route("/")
 * @author Austin Shinpaugh <ashinpuagh@ou.edu>
 */
class DefaultController extends AbstractController
{
    /**
     * Page index.
     * 
     * @Route("/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('ATSScheduleBundle:Default:index.html.twig');
    }
}
