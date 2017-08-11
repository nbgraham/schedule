<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Homepage.
 * 
 * Actions here are not handled by FOSRest.
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
     * @return Response
     */
    public function indexAction()
    {
        $response = $this->render('ATSScheduleBundle:Default:index.html.twig');
        
        return $response
            ->setPublic()
            ->setSharedMaxAge(0)
            ->setMaxAge(0)
        ;
    }
    
    /**
     * The Edge Side Includes for caching purposes.
     * 
     * @Method({"GET"})
     * @Route("/esi")
     * 
     * @return Response
     */
    public function esiAction()
    {
        $update   = $this->getLastUpdateLog();
        $ttl      = $this->getMaxAge($update);
        $response = $this->render('@ATSSchedule/Default/esi.html.twig', [
            'update' => $update,
        ]);
        
        return $response->setCache([
            'public'        => true,
            'max_age'       => $ttl,
            's_maxage'      => $ttl,
            'last_modified' => $update->getStart(),
        ]);
    }
    
    /**
     * Fetch the most recent update log.
     * 
     * @return UpdateLog
     */
    private function getLastUpdateLog()
    {
        $repo   = $this->getDoctrine()->getRepository(UpdateLog::class);
        $update = $repo->findBy([], ['start' => 'DESC'], 1);
        
        return current($update);
    }
    
    /**
     * Return the number of seconds until the next update.
     * 
     * @param UpdateLog $update
     * 
     * @return int
     */
    private function getMaxAge(UpdateLog $update)
    {
        $import_hour = $this->getParameter('import_hour');
        $import_min  = $this->getParameter('import_minute');
        
        $now   = new \DateTime();
        $today = clone $now;
        $today->setTime($import_hour, $import_min);
        
        if ($now < $today) {
            // The update hasn't passed yet today.
            return $today->format('U');
        }
        
        if (UpdateLog::STARTED === $update->getStatus()) {
            // The update is currently in progress. Only save this response for 2.5 seconds.
            return (int) $now->format('U') + 2500;
        }
        
        // The update completed, and the proxy can now store the response for this long.
        $future = clone $now;
        $future->setTimestamp(strtotime('next day'));
        $future->setTime($import_hour, $import_min);
        
        return $future->format('U');
    }
}
