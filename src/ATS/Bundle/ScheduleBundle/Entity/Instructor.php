<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 */
class Instructor extends AbstractEntity
{
    /**
     * @Serializer\MaxDepth(2)
     * 
     * @ORM\OneToMany(targetEntity="ClassEvent", mappedBy="instructor")
     * @var ClassEvent[]
     */
    protected $classes;
    
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
     * Instructor constructor.
     *
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->classes = new ArrayCollection();
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
     * @return ClassEvent[]
     */
    public function getClasses()
    {
        return $this->classes;
    }
    
    /**
     * @param ClassEvent $event
     *
     * @return Instructor
     */
    public function addClass(ClassEvent $event)
    {
        $this->classes->add($event);
        
        return $this;
    }
    
    /**
     * @param ClassEvent $class
     *
     * @return Instructor
     */
    public function removeClass(ClassEvent $class)
    {
        $this->classes->removeElement($class);
        
        return $this;
    }
}