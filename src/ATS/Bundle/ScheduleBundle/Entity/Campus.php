<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="campus", indexes={
 *    @ORM\Index(name="idx_name", columns={"name"})
 * })
 */
class Campus extends AbstractEntity
{
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="Building", mappedBy="campus", cascade={"ALL"})
     * @var Building[]
     */
    protected $buildings;
    
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="ClassEvent", mappedBy="campus")
     * @var ClassEvent[]
     */
    protected $classes;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * 
     * @ORM\Column(type="string")
     * @var String
     */
    protected $name;
    
    /**
     * Campus constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
        
        $this->buildings = new ArrayCollection();
        $this->classes   = new ArrayCollection();
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return Campus
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param mixed $name
     *
     * @return Campus
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return Building[]
     */
    public function getBuildings()
    {
        return $this->buildings;
    }
    
    /**
     * @param Building $building
     *
     * @return Campus
     */
    public function addBuilding(Building $building)
    {
        $this->buildings->add($building);
        $building->setCampus($this);
        
        return $this;
    }
    
    /**
     * @param Building $building
     *
     * @return Campus
     */
    public function removeBuilding(Building $building)
    {
        $this->buildings->remove($building);
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getClasses()
    {
        return $this->classes;
    }
    
    /**
     * @param ClassEvent $event
     *
     * @return Campus
     */
    public function addClass(ClassEvent $event)
    {
        $this->classes->add($event);
        
        return $this;
    }
}