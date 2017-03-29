<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;

class InstructorsController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetches all the known instructors.
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function cgetAction()
    {
        $instructors = $this->getRepo('ATSScheduleBundle:Instructor')
            ->findAll()
        ;
        
        return ['instructors' => $instructors];
    }
    
    /**
     * Get all the classes taught by an instructor.
     *
     * @View(templateVar="instructor", serializerEnableMaxDepthChecks=true)
     * 
     * @param int $id
     *
     * @return mixed
     */
    public function getAction($id)
    {
        $instructor = $this->getRepo('ATSScheduleBundle:Instructor')
            ->find($id)
        ;
        
        if (!$instructor instanceof Instructor) {
            return null;
        }
        
        return ['instructor' => $instructor];
    }
}
