<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="building", indexes={
 *    @ORM\Index(name="idx_name", columns={"name"})
 * })
 */
class Building extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Campus", inversedBy="buildings", cascade={"all"})
     * 
     * @var Campus
     */
    protected $campus;
    
    /**
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="Room", mappedBy="building", cascade={"all"})
     * @var Room[]
     */
    protected $rooms;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $name;
    
    /**
     * Building constructor.
     *
     * @param Campus $campus
     * @param string $name
     */
    public function __construct(Campus $campus, $name)
    {
        $this
            ->setCampus($campus)
            ->setName($name)
        ;
        
        $this->rooms = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'name'   => $this->name,
            'campus' => $this->campus->getName(),
        ];
    }
    
    /**
     * @return Campus
     */
    public function getCampus()
    {
        return $this->campus;
    }
    
    /**
     * @param Campus $campus
     *
     * @return Building
     */
    public function setCampus(Campus $campus)
    {
        $this->campus = $campus;
        
        return $this;
    }
    
    /**
     * @return Room[]
     */
    public function getRooms()
    {
        return $this->rooms;
    }
    
    /**
     * @param Room $room
     *
     * @return Building
     */
    public function addRoom(Room $room)
    {
        $this->rooms->add($room);
        
        return $this;
    }
    
    /**
     * @param Room $room
     *
     * @return Building
     */
    public function removeRoom(Room $room)
    {
        $this->rooms->removeElement($room);
        
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
     * @param int $id
     *
     * @return Building
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     *
     * @return Building
     */
    public function setName($name)
    {
        $this->name = Encoding::toUTF8($name);
        
        return $this;
    }
}