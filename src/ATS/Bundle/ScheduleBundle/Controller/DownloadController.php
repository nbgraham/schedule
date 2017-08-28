<?php

namespace ATS\Bundle\ScheduleBundle\Controller;

use ATS\Bundle\ScheduleBundle\Entity\Section;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
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
    public function getExportAction(Request $request)
    {
        $response = $this->forward('ATSScheduleBundle:Section:get', [], $request->query->all());
        $sections = json_decode($response->getContent(), true);
        
        $doctrine = $this->getDoctrine();
        $response = new StreamedResponse();
        
        $response->setCallback(function () use ($doctrine, $sections) {
            $handle = fopen('php://output', 'w+');
            
            fputcsv($handle, ['Subject', 'Course', 'Section', 'CRN', 'Title', 'Instructor', 'Instructor ID', 'Max', 'Start Date', 'End Date', 'Bldg', 'Rm', 'Days', 'Start', 'End'], ',');
            
            /* @var Section $section */
            foreach ($sections['sections'] as $section) {
                $sdate = new \DateTime($section['start']);
                $edate = new \DateTime($section['end']);
                
                fputcsv($handle, [
                    $section['subject']['name'],
                    $section['course']['number'],
                    $section['number'],
                    $section['crn'],
                    $section['course']['name'],
                    $section['instructor']['name'],
                    $section['instructor']['id'],
                    $section['maximum_enrollment'],
                    $sdate->format('n/j/Y'),
                    $edate->format('n/j/Y'),
                    $section['building']['name'],
                    $section['room']['number'],
                    $section['days'],
                    $section['start_time'],
                    $section['end_time'],
                ], ',');
            }
            
            fclose($handle);
        });
        
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="classplan_export.csv"');
    
        return $response;
    }
}
