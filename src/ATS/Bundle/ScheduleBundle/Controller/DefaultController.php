<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
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
     * @Route(name="ats_home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $repo    = $manager->getRepository(UpdateLog::class);
        $update  = $repo->findBy([], ['start' => 'DESC'], 1);
        
        return $this->render('ATSScheduleBundle:Default:index.html.twig', [
            'update' => current($update),
        ]);
    }
}
