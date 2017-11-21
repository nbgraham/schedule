<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Section;
use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * The endpoint used when interacting with events.
 * 
 * @ApiDoc(
 *     description="The main controller used by the front-end API."
 * )
 * 
 * @RouteResource("section", pluralize=false)
 * @Prefix("/coreq/section")
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class SectionController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetch a subset of sections based on the provided filter criteria.
     * 
     * @ApiDoc(
     *     resource=true
     * )
     * 
     * @Route("")
     * @QueryParam(name="b", nullable=false, description="The block ID(s).")
     * @QueryParam(name="s", nullable=true,  description="Optional. The subject ID(s).")
     * @QueryParam(name="i", nullable=true,  description="Optional. The instructor ID(s) to filter on.")
     * @QueryParam(name="n", nullable=true,  description="Optional. The course number ID(s) to filter on.")
     * @QueryParam(name="u", nullable=false, description="The date that the other IDs were created on.")
     * 
     * @View(serializerEnableMaxDepthChecks=true)
     * @Cache(public=true, expires="+10 minutes", maxage=600, smaxage=600)
     * 
     * @param Request      $request
     * @param ParamFetcher $fetcher
     * 
     * @return array
     */
    public function getAction(Request $request, ParamFetcher $fetcher)
    {
        $block       = null;
        $subject     = null;
        $course      = null;
        $instructor  = null;
        
        // if (!$this->checkTimestamp($fetcher->get('u'))) {
        //     throw new ConflictHttpException();
        // }
        
        if ($block_id = $fetcher->get('b')) {
            $block = $this->getRepo('ATSScheduleBundle:TermBlock')
                ->findById($block_id)
            ;
        }
        
        if ($instructor_id = $fetcher->get('i')) {
            $instructor = $this->getRepo('ATSScheduleBundle:Instructor')
                ->findById($instructor_id)
            ;
        }
        
        if ($subject_id = $fetcher->get('s')) {
            $subject = $this->getRepo('ATSScheduleBundle:Subject')
                ->findById($subject_id)
            ;
        }
        
        if ($course_id = $fetcher->get('n')) {
            $course = $this->getRepo('ATSScheduleBundle:Course')
                ->findById($course_id)
            ;
        }
        
        $this->get('session')->set('last_query', $request->getQueryString());
        
        return ['sections' => $this->getRepo(Section::class)
            ->findBy(array_filter([
                'block'      => $block,
                'subject'    => $subject,
                'course'     => $course,
                'instructor' => $instructor,
            ])
        )];
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
        $update    = $this->getLastUpdateLog();
        $timestamp = strtotime($timestamp);
        
        return $update->getStart()->getTimestamp() === $timestamp;
    }
}
