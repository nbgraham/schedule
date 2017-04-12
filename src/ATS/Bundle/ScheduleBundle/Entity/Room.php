<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="room", indexes={
 *    @ORM\Index(name="idx_number", columns={"number"})
 * })
 */
class Room extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Building", inversedBy="rooms", fetch="EXTRA_LAZY", cascade={"persist"})
     * 
     * @var Building
     */
    protected $building;
    
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="ClassEvent", mappedBy="room")
     * @var ClassEvent[]
     */
    protected $classes;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     * 
     * @var string
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $number;
    
    /**
     * Room constructor.
     *
     * @param Building $building
     * @param string   $number
     */
    public function __construct(Building $building, $number)
    {
        $this->number   = $number;
        $this->building = $building;
        $this->classes  = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return [
            'number'   => $this->number,
            'building' => $this->getBuilding()->getName(),
        ];
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
    
    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * @param mixed $number
     *
     * @return Room
     */
    public function setNumber($number)
    {
        $this->number = $number;
        
        return $this;
    }
}