<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Section;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller responsible for generating CSV exports.
 * 
 * @ApiDoc()
 * @RouteResource("download", pluralize=false)
 */
class DownloadController extends AbstractController
{
    /**
     * Exports the section information.
     * 
     * @see https://vauly.com/symfony2-export-csv
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getExportAction()
    {
        $section_ids = $this->get('session')->get('last_results');
        $doctrine    = $this->getDoctrine();
        $response    = new StreamedResponse();
        
        $response->setCallback(function () use ($doctrine, $section_ids) {
            $handle  = fopen('php://output', 'w+');
    
            fputcsv($handle, ['Subject', 'Course', 'Section', 'CRN', 'Title', 'Instructor', 'Instructor ID', 'Max', 'Start Date', 'End Date', 'Bldg', 'Rm', 'Days', 'Start', 'End'],',');
            
            $sections = $doctrine->getRepository('ATSScheduleBundle:Section')
                ->findBy([
                    'id' => $section_ids,
                ])
            ;
            
            /* @var Section $section */
            foreach ($sections as $section) {
                fputcsv($handle, [
                    $section->getSubject()->getName(),
                    $section->getCourse()->getNumber(),
                    $section->getSection(),
                    $section->getCrn(),
                    $section->getCourse()->getName(),
                    $section->getInstructor()->getName(),
                    $section->getInstructor()->getId(),
                    $section->getMaximumEnrollment(),
                    $section->getStartDate()->format('n/j/Y'),
                    $section->getEndDate()->format('n/j/Y'),
                    $section->getBuilding()->getName(),
                    $section->getRoom()->getNumber(),
                    $section->getDays(),
                    $section->getStartTime(),
                    $section->getEndTime(),
                ], ',');
            }
            
            fclose($handle);
        });
    
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="schedule_export.csv"');
    
        return $response;
    }
}
