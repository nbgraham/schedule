<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Campus extends AbstractEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Building", mappedBy="campus")
     * @var Building[]
     */
    protected $buildings;
    
    /**
     * @ORM\ManyToMany(targetEntity="Event")
     * @ORM\JoinTable("campus_classes",
     *    joinColumns={
     *      @ORM\JoinColumn(name="campus_id", referencedColumnName="id")
     *    },
     *    inverseJoinColumns={
     *      @ORM\JoinColumn(name="course_crn", referencedColumnName="crn", unique=true)
     *    }
     * )
     * @var Event[]
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
    protected $display_name;
    
    /**
     * Campus constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setDisplayName($name);
        
        $this->buildings = new ArrayCollection();
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
    public function getDisplayName()
    {
        return $this->display_name;
    }
    
    /**
     * @param mixed $display_name
     *
     * @return Campus
     */
    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;
        
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
}