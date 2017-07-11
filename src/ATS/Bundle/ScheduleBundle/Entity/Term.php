<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="term", indexes={
 *    @ORM\Index(name="idx_year_semester", columns={"year", "semester"}),
 * })
 */
class Term extends AbstractEntity
{
    /**
     * @Serializer\MaxDepth(2)
     * 
     * @ORM\OneToMany(targetEntity="TermBlock", mappedBy="term", fetch="EAGER", cascade={"detach"})
     * @var TermBlock[]
     */
    protected $blocks;
    
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
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $year;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $semester;
    
    /**
     * Term constructor.
     *
     * @param string  $name
     * @param integer $year
     * @param string  $semester
     */
    public function __construct($name, $year, $semester)
    {
        $this->setName($name);
        
        $this->year     = $year;
        $this->semester = $semester;
        $this->blocks   = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'year'     => $this->year,
            'semester' => $this->semester,
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
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return Term
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return TermBlock[]|ArrayCollection
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
    
    /**
     * @param TermBlock $block
     *
     * @return Term
     */
    public function addBlock(TermBlock $block)
    {
        $this->blocks->add($block);
        $block->setTerm($this);
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }
    
    /**
     * @return String
     */
    public function getSemester()
    {
        return $this->semester;
    }
}