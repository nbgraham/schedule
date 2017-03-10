<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Term extends AbstractEntity
{
    /**
     * @ORM\ManyToMany(targetEntity="TermBlock", mappedBy="term")
     * @var TermBlock[]
     */
    protected $blocks;
    
    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="term")
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
     * @ORM\Column(type="string")
     * @var String
     */
    protected $display_name;
    
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
     * @param $name
     */
    public function __construct($name, $year, $semester)
    {
        $this->setDisplayName($name);
        
        $this->blocks  = new ArrayCollection();
        $this->terms   = new ArrayCollection();
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
    public function getDisplayName()
    {
        return $this->display_name;
    }
    
    /**
     * @param String $display_name
     *
     * @return Term
     */
    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;
        
        return $this;
    }
    
    /**
     * @return TermBlock[]
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
    public function addBlock($block)
    {
        $this->blocks->add($block);
        
        return $this;
    }
    
    /**
     * @return Event[]
     */
    public function getClasses()
    {
        return $this->classes;
    }
    
    /**
     * @param Event $class
     *
     * @return Term
     */
    public function addClass(Event $class)
    {
        $this->classes->add($class);
        
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