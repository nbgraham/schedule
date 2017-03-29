<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;

/**
 * Represents an entry from The Book.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class TheBook
{
    protected static $instance;
    protected $headers;
    
    protected function __construct(array $headers)
    {
        $this->headers = $headers;
    }
    
    public static function init(array $headers)
    {
        if (static::$instance instanceof TheBook) {
            return static::$instance;
        }
        
        return new static($headers);
    }
    
    public function synchronizeHeaders()
    {
        
    }
    
    public function getDefaultHeaders()
    {
        return ["Term", "Subject", "Course Number", "Section", "CRN", "Title", "Primary Instructor", "Instructor ID", "Status", "Campus", "Grade Mode", "Maximum Enrollment", "Actual Enrollment", "Seats Available", "Dean's Expected Enrollment", "Waitlist", "Start Date", "End Date", "Building", "Room", "Days", "Start Time", "End Time", "College", "Department", "Fee #1 Detail Code", "Fee #1 Name", "Fee #1 Type", "Fee #1 Amount", "Fee #2 Detail Code", "Fee #2 Name", "Fee #2 Type", "Fee #2 Amount", "Instruction Method", "Schedule Code", "Part of Term", "Level", "Gened", "Attributes"];
    }
}