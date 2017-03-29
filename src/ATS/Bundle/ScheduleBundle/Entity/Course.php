<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="course", indexes={
 *    @ORM\Index(name="idx_subject", columns={"subject"}),
 *    @ORM\Index(name="idx_subject_num", columns={"subject", "number"})
 * })
 */
class Course extends AbstractEntity
{
    //// Term, Subject, Course Number, Section, CRN, Title, Primary Instructor, Instructor ID, Status, Campus, Grade Mode, Maximum Enrollment, Actual Enrollment, Seats Available, Enrollment Waitlist, Start Date, End Date, Building, Room, Days, Start Time, End Time, College, Department, Schedule Code, Part of Term, Level
    
    /**
     * @Serializer\MaxDepth(2)
     * 
     * @ORM\OneToMany(targetEntity="ClassEvent", mappedBy="course")
     * @var ClassEvent[]
     */
    protected $classes;
    
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     * 
     * @var Integer
     */
    protected $number;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $subject;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $title;
    
    /**
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $maximum_enrollment;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $level;
    
    /**
     * Course constructor.
     * 
     * @param string  $subject
     * @param integer $number
     */
    public function __construct($subject, $number)
    {
        $this->subject = $subject;
        $this->number  = $number;
        
        $this->classes = new ArrayCollection();
    }
    
    /**
     * @return ClassEvent[]
     */
    public function getClasses()
    {
        return $this->classes;
    }
    
    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return int
     */
    public function getMaximumEnrollment()
    {
        return $this->maximum_enrollment;
    }
    
    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }
    
    /**
     * @param int $number
     *
     * @return Course
     */
    public function setNumber($number)
    {
        $this->number = $number;
        
        return $this;
    }
    
    /**
     * @param string $subject
     *
     * @return Course
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        
        return $this;
    }
    
    /**
     * @param string $title
     *
     * @return Course
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * @param int $maximum_enrollment
     *
     * @return Course
     */
    public function setMaximumEnrollment($maximum_enrollment)
    {
        $this->maximum_enrollment = $maximum_enrollment;
        
        return $this;
    }
    
    /**
     * @param string $level
     *
     * @return Course
     */
    public function setLevel($level)
    {
        $this->level = $level;
        
        return $this;
    }
    
    /**
     * @param ClassEvent $event
     *
     * @return $this
     */
    public function addClass(ClassEvent $event)
    {
        $this->classes->add($event);
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}