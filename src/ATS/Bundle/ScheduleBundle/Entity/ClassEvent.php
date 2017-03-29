<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * The Event table represents instances of course classes.
 * 
 * @ORM\Entity()
 * @ORM\Table(name="class")
 */
class ClassEvent extends AbstractEntity
{
    const CANCELLED = -1;
    const INACTIVE  = 0;
    const ACTIVE    = 1;
    
    /**
     * @ORM\ManyToOne(targetEntity="Course", inversedBy="classes")
     * @var Course
     */
    protected $course;
    
    /**
     * @ORM\ManyToOne(targetEntity="Campus", inversedBy="classes")
     * @var Campus
     */
    protected $campus;
    
    /**
     * @Serializer\MaxDepth(1)
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="classes")
     * @var Room
     */
    protected $room;
    
    /**
     * @ORM\ManyToOne(targetEntity="Instructor", inversedBy="classes")
     * 
     * @var Instructor
     */
    protected $instructor;
    
    /**
     * @ORM\ManyToOne(targetEntity="TermBlock", inversedBy="classes")
     * @var TermBlock
     */
    protected $block;
    
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
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $start_date;
    
    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $end_date;
    
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
     * @return ClassEvent
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
     * @return ClassEvent
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
     * @return ClassEvent
     */
    public function setInstructor(Instructor $instructor)
    {
        $this->instructor = $instructor;
        
        return $this;
    }
    
    /**
     * @return TermBlock
     */
    public function getBlock()
    {
        return $this->block;
    }
    
    /**
     * @param TermBlock $block
     *
     * @return ClassEvent
     */
    public function setBlock(TermBlock $block)
    {
        $this->block = $block;
        
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
     * @return ClassEvent
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
     * Find the verbal version of the status.
     * 
     * @return string
     */
    public function getVerbalStatus()
    {
        switch ($this->status) {
            case static::CANCELLED:
                return 'cancelled';
            case static::ACTIVE:
                return 'active';
            default:
                return 'inactive';
        }
    }
    
    /**
     * @param int $status
     *
     * @return ClassEvent
     */
    public function setStatus($status)
    {
        if (!is_string($status) && in_array($status, [-1, 0, 1])) {
            $this->status = $status;
            return $this;
        }
        
        $status = strtolower($status);
        if (!in_array($status, ['active', 'inactive', 'cancelled'])) {
            throw new \InvalidArgumentException('Invalid status: ' . $status);
        }
        
        switch ($status) {
            case 'active':
                $this->status = static::ACTIVE;
                break;
            case 'inactive':
                $this->status = static::INACTIVE;
                break;
            default:
                $this->status = static::CANCELLED;
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
     * @return ClassEvent
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
     * @return ClassEvent
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
     * @return ClassEvent
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
     * @return ClassEvent
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
     * @return ClassEvent
     */
    public function setNumEnrolled($num_enrolled)
    {
        $this->num_enrolled = $num_enrolled;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }
    
    /**
     * @param \DateTime $start_date
     *
     * @return ClassEvent
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }
    
    /**
     * @param \DateTime $end_date
     *
     * @return ClassEvent
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
        
        return $this;
    }
    
    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }
    
    /**
     * @param Room $room
     *
     * @return ClassEvent
     */
    public function setRoom(Room $room)
    {
        $this->room = $room;
        
        return $this;
    }
}