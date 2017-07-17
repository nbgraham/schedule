<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
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
     * @ORM\OneToMany(targetEntity="Building", mappedBy="campus", cascade={"all"})
     * @var Building[]
     */
    protected $buildings;
    
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="Section", mappedBy="campus")
     * @var Section[]
     */
    protected $sections;
    
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
        $this->sections  = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'name' => $this->name,
        ];
    }
    
    /**
     * @Serializer\VirtualProperty()
     */
    public function getDisplayName()
    {
        switch ($this->getName()) {
            case 'N':
                return 'Norman - Main Campus';
            case 'T':
                return 'Tulsa Campus';
            case 'I':
                return 'Independent Campus';
            case 'L':
                return 'Liberal Studies';
            case 'O':
                return 'Outreach Academic Programs';
            case 'R':
                return 'Redlands at Norman CCE';
            case 'S':
                return 'Intersession';
            default:
                return $this->getName();
        }
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
        $this->name = Encoding::toUTF8($name);
        
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
    public function getSections()
    {
        return $this->sections;
    }
    
    /**
     * @param Section $event
     *
     * @return Campus
     */
    public function addSection(Section $section)
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
        }
        
        return $this;
    }
}