<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Room extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Building", inversedBy="rooms")
     * 
     * @var Building
     */
    protected $building;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="string")
     * 
     * @var string
     */
    protected $id;
    
    /**
     * Room constructor.
     *
     * @param Building $building
     * @param string   $id
     */
    public function __construct(Building $building, $id)
    {
        $this->id       = $id;
        $this->building = $building;
    }
    
    /**
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
    }
    
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}