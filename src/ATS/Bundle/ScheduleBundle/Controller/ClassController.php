<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Section;
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
 * @ApiDoc(
 *     description="The main controller used by the front-end API."
 * )
 * 
 * @RouteResource("class", pluralize=false)
 * 
 * @Prefix("/class")
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class ClassController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetch a subset of classes based on the provided filter criteria.
     * 
     * @ApiDoc(
     *     resource=true
     * )
     * 
     * @Route("")
     * @QueryParam(name="block",      nullable=false, description="The block ID(s).")
     * @QueryParam(name="subject",    nullable=true,  description="Optional. The subject ID(s).")
     * @QueryParam(name="instructor", nullable=true,  description="Optional. The instructor ID(s) to filter on.")
     * @QueryParam(name="number",     nullable=true,  description="Optional. The course number ID(s) to filter on.")
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function getAction(ParamFetcher $fetcher)
    {
        $block      = null;
        $subject    = null;
        $course     = null;
        $instructor = null;
        
        if ($block_id = $fetcher->get('block')) {
            $block = $this->getRepo('ATSScheduleBundle:TermBlock')
                ->findById($block_id)
            ;
        }
        
        if ($instructor_id = $fetcher->get('instructor')) {
            $instructor = $this->getRepo('ATSScheduleBundle:Instructor')
                ->findById($instructor_id)
            ;
        }
        
        if ($subject_id = $fetcher->get('subject')) {
            $subject = $this->getRepo('ATSScheduleBundle:Subject')
                ->findById($subject_id)
            ;
        }
        
        if ($course_id = $fetcher->get('number')) {
            $course = $this->getRepo('ATSScheduleBundle:Course')
                ->findById($course_id)
            ;
        }
        
        return [
            'classes' => $this->getRepo('ATSScheduleBundle:Section')
                ->findBy(array_filter([
                    'block'      => $block,
                    'subject'    => $subject,
                    'course'     => $course,
                    'instructor' => $instructor,
                ])),
        ];
    }
}
