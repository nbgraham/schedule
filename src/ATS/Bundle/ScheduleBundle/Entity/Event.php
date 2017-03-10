<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * The Event table represents instances of course classes.
 * 
 * @ORM\Entity()
 * @ORM\Table(name="class")
 */
class Event extends AbstractEntity
{
    const CANCELED = -1;
    const INACTIVE = 0;
    const ACTIVE   = 1;
    
    /**
     * @ORM\ManyToOne(targetEntity="Course", inversedBy="classes")
     * @var Course
     */
    protected $course;
    
    /**
     * @ORM\ManyToMany(targetEntity="Campus")
     * @ORM\JoinTable(
     *    joinColumns={
     *      @ORM\JoinColumn(name="course_crn", referencedColumnName="crn")
     *    },
     *    inverseJoinColumns={
     *      @ORM\JoinColumn(name="campus_id", referencedColumnName="id", unique=true)
     *    }
     * )
     * @var Campus
     */
    protected $campus;
    
    /**
     * @ORM\ManyToOne(targetEntity="Instructor", inversedBy="classes")
     * 
     * @var Instructor
     */
    protected $instructor;
    
    /**
     * @ORM\ManyToOne(targetEntity="Term", inversedBy="classes")
     * @var Term
     */
    protected $term;
    
    /**
     * The Event unique ID.
     * 
     * @ORM\Id()
     * @ORM\Column(name="crn", type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     * 
     * @var Integer
     */
    protected $crn;
    
    /**
     * @ORM\Column(type="smallint")
     * @var Integer
     */
    protected $status;
    
    /**
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $section;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var String
     */
    protected $days;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $start_time;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $end_time;
    
    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $num_enrolled;
    
    public function __construct()
    {
        $this->campus = new ArrayCollection();
    }
    
    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }
    
    /**
     * @param Course $course
     *
     * @return Event
     */
    public function setCourse(Course $course)
    {
        $this->course = $course;
        
        return $this;
    }
    
    /**
     * @return Campus
     */
    public function getCampus()
    {
        return $this->campus;
    }
    
    /**
     * @param Campus $campus
     *
     * @return Event
     */
    public function setCampus(Campus $campus)
    {
        $this->campus = $campus;
        
        return $this;
    }
    
    /**
     * @return Instructor
     */
    public function getInstructor()
    {
        return $this->instructor;
    }
    
    /**
     * @param Instructor $instructor
     *
     * @return Event
     */
    public function setInstructor(Instructor $instructor)
    {
        $this->instructor = $instructor;
        
        return $this;
    }
    
    /**
     * @return Term
     */
    public function getTerm()
    {
        return $this->term;
    }
    
    /**
     * @param Term $term
     *
     * @return Event
     */
    public function setTerm(Term $term)
    {
        $this->term = $term;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getCrn()
    {
        return $this->crn;
    }
    
    /**
     * @param int $crn
     *
     * @return Event
     */
    public function setCrn($crn)
    {
        $this->crn = $crn;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @param int $status
     *
     * @return Event
     */
    public function setStatus($status)
    {
        if (!is_string($status)) {
            $this->status = $status;
            return $this;
        }
        
        if (!in_array(strtolower($status), ['active', 'inactive', 'canceled'])) {
            throw new \InvalidArgumentException();
        }
        
        switch ($status) {
            case 'active':
                $this->status = static::ACTIVE;
                break;
            case 'inactive':
                $this->status = static::INACTIVE;
                break;
            default:
                $this->status = static::CANCELED;
        }
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getSection()
    {
        return $this->section;
    }
    
    /**
     * @param int $section
     *
     * @return Event
     */
    public function setSection($section)
    {
        $this->section = $section;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getDays()
    {
        return $this->days;
    }
    
    /**
     * @param String $days
     *
     * @return Event
     */
    public function setDays($days)
    {
        $this->days = $days;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }
    
    /**
     * @param string $start_time
     *
     * @return Event
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEndTime()
    {
        return $this->end_time;
    }
    
    /**
     * @param string $end_time
     *
     * @return Event
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getNumEnrolled()
    {
        return $this->num_enrolled;
    }
    
    /**
     * @param int $num_enrolled
     *
     * @return Event
     */
    public function setNumEnrolled($num_enrolled)
    {
        $this->num_enrolled = $num_enrolled;
        
        return $this;
    }
}