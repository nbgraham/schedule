<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;

/**
 * The endpoint used when interacting with events.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class ClassesController extends AbstractController implements ClassResourceInterface
{
    public function getClassesAction($year, $semester)
    {
        
    }
    
    /**
     * @View(templateVar="instructor", serializerEnableMaxDepthChecks=true)
     * 
     * @param string $subject
     *
     * @return array
     */
    public function getSubjectAction($subject)
    {
        $courses = $this->getRepo('ATSScheduleBundle:Course')
            ->findBy([
                'subject' => $subject,
            ])
        ;
        
        return $courses;
    }
}
