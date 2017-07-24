<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
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
     * @ORM\ManyToOne(targetEntity="Building", inversedBy="rooms", fetch="EAGER")
     * 
     * @var Building
     */
    protected $building;
    
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="Section", mappedBy="room")
     * @var Section[]
     */
    protected $sections;
    
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
        $this->setNumber($number);
        
        $this->building = $building;
        $this->sections = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
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
        $this->number = Encoding::toUTF8($number);
        
        return $this;
    }
}