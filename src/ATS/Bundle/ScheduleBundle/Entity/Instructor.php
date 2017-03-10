<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Instructor extends AbstractEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="bigint")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name", type="string")
     * @var String
     */
    protected $name;
    
    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="instructor")
     * @var Event[]
     */
    protected $classes;
    
    /**
     * Instructor constructor.
     *
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->courses = new ArrayCollection();
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return Instructor
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return Course[]
     */
    public function getCourses()
    {
        return $this->courses;
    }
    
    /**
     * @param Course $course
     *
     * @return Instructor
     */
    public function addCourse(Course $course)
    {
        $this->courses->add($course);
        
        return $this;
    }
    
    /**
     * @param Course $course
     *
     * @return Instructor
     */
    public function removeCourse(Course $course)
    {
        $this->courses->removeElement($course);
        
        return $this;
    }
}