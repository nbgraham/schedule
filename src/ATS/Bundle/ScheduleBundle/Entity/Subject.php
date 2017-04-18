<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="subject", indexes={
 *    @ORM\Index(name="idx_name", columns={"name"})
 * })
 */
class Subject extends AbstractEntity
{
    /**
     * @Serializer\MaxDepth(2)
     * 
     * @ORM\OneToMany(targetEntity="Course", mappedBy="subject", cascade={"persist"}, fetch="EAGER")
     * @var Course[]
     */
    protected $courses;
    
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="ClassEvent", mappedBy="subject")
     * @var ClassEvent[]
     */
    protected $classes;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $name;
    
    /**
     * Subject constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name    = $name;
        $this->courses = new ArrayCollection();
        $this->classes = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return [
            'name' => $this->name,
        ];
    }
    
    /**
     * @return Course[]|ArrayCollection
     */
    public function getCourses()
    {
        return $this->courses;
    }
    
    /**
     * Add a course.
     * 
     * @param Course $course
     *
     * @return Subject
     */
    public function addCourse(Course $course)
    {
        $this->courses->add($course);
        
        if (!$course->getSubject()) {
            $course->setSubject($this);
        }
        
        return $this;
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
     * @return ClassEvent[]|ArrayCollection
     */
    public function getClasses()
    {
        return $this->classes;
    }
    
    /**
     * @param ClassEvent $event
     *
     * @return Subject
     */
    public function addClass(ClassEvent $event)
    {
        $this->classes->add($event);
        
        if (!$event->getSubject()) {
            $event->setSubject($this);
        }
        
        return $this;
    }
}