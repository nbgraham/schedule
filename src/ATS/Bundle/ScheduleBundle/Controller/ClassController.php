<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\ClassEvent;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Subject;
use ATS\Bundle\ScheduleBundle\Entity\Term;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * The endpoint used when interacting with events.
 * 
 * @ApiDoc()
 * 
 * @RouteResource("class", pluralize=false)
 * 
 * #Prefix("/class")
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class ClassController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetch a class.
     * 
     * @QueryParam(name="block",      nullable=true)
     * @QueryParam(name="subject",    nullable=true)
     * @QueryParam(name="instructor", nullable=true)
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function getAction(ParamFetcher $fetcher)
    {
        $block      = null;
        $instructor = null;
        $subject    = null;
        
        if ($block_id = $fetcher->get('block')) {
            $block = $this->getRepo('ATSScheduleBundle:TermBlock')
                ->find($block_id)
            ;
        }
        
        if ($instructor_id = $fetcher->get('instructor')) {
            $instructor = $this->getRepo('ATSScheduleBundle:Instructor')
                ->find($instructor_id)
            ;
        }
        
        if ($subject_id = $fetcher->get('subject')) {
            $subject = $this->getRepo('ATSScheduleBundle:Subject')
                ->find($subject_id)
            ;
        }
        
        return [
            'classes' => $this->getRepo('ATSScheduleBundle:ClassEvent')
                ->findBy(array_filter([
                    'block'      => $block,
                    'subject'    => $subject,
                    'instructor' => $instructor
                ])),
        ];
    }
    
    
    /**
     * @Route(path="/{block_id}/{subject_id}")
     * 
     * @ParamConverter("block", class="ATSScheduleBundle:TermBlock", options={
     *    "mapping"={
     *      "block_id": "id"
     *    }
     * })
     * 
     * @ParamConverter("subject", class="ATSScheduleBundle:Subject", options={
     *    "mapping"={
     *      "subject_id": "id"
     *    }
     * })
     * 
     * @QueryParam(name="instructor", nullable=true)
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     * 
     * @return array
     */
    public function getSubjectAction(ParamFetcher $fetcher, TermBlock $block, Subject $subject)
    {
        /*$repo   = $this->getRepo('ATSScheduleBundle:ClassEvent');
        $blocks = $term->getBlocks();
        $output = [];
        
        foreach ($blocks as $block) {
            $output = array_merge($output, $repo->findBy([
                'subject' => $subject,
                'block'   => $block,
            ]));
        }*/
        
        $instructor = null;
        if ($instructor_id = $fetcher->get('instructor')) {
            $instructor = $this->getRepo('ATSScheduleBundle:Instructor')
                ->find($instructor_id)
            ;
        }
        
        return [
            'classes' => $this->getRepo('ATSScheduleBundle:ClassEvent')
                ->findBy(array_filter([
                    'block'      => $block,
                    'subject'    => $subject,
                    'instructor' => $instructor
                ])),
        ];
    }
    
}
