<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Section;
use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
     * @QueryParam(name="block",       nullable=false, description="The block ID(s).")
     * @QueryParam(name="subject",     nullable=true,  description="Optional. The subject ID(s).")
     * @QueryParam(name="instructor",  nullable=true,  description="Optional. The instructor ID(s) to filter on.")
     * @QueryParam(name="number",      nullable=true,  description="Optional. The course number ID(s) to filter on.")
     * @QueryParam(name="last_update", nullable=false, description="The date that the other IDs were created on.")
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function getAction(ParamFetcher $fetcher)
    {
        $block       = null;
        $subject     = null;
        $course      = null;
        $instructor  = null;
        
        if (!$this->checkTimestamp($fetcher->get('last_update'))) {
            throw new ConflictHttpException();
        }
        
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
        
        $ids     = [];
        $classes = $this->getRepo('ATSScheduleBundle:Section')
            ->findBy(array_filter([
                'block'      => $block,
                'subject'    => $subject,
                'course'     => $course,
                'instructor' => $instructor,
            ])
        );
        
        foreach ($classes as $section) {
            /* @var Section $section */
            $ids[] = $section->getId();
        }
        
        $this->get('session')->set('last_results', $ids);
        
        return ['classes' => $classes];
    }
    
    /**
     * Verify that the timestamp of the last update matches the most recent
     * UpdateLog entry.
     * 
     * Since the Term / Instructor / Course data is inserted into the page
     * on page load, their id's could differ after an update occurs and return
     * mismatched results.
     * 
     * @param string $timestamp
     *
     * @return bool
     */
    private function checkTimestamp($timestamp)
    {
        $repo   = $this->getRepo(UpdateLog::class);
        $update = $repo->findBy([], ['start' => 'DESC'], 1);
        
        $update    = current($update);
        $timestamp = strtotime($timestamp);
        
        return $update->getStart()->getTimestamp() === $timestamp;
    }
}
