<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TermBlock extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Term", inversedBy="blocks", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var Term
     */
    protected $term;
    
    /**
     * @ORM\OneToMany(targetEntity="ClassEvent", mappedBy="block", fetch="EXTRA_LAZY")
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
     * @ORM\Column(type="string")
     * @var String
     */
    protected $name;
    
    /**
     * TermBlock constructor.
     *
     * @param Term   $term
     * @param string $name
     */
    public function __construct(Term $term, $name)
    {
        $this
            ->setTerm($term)
            ->setName($name)
        ;
        
        $this->classes = new ArrayCollection();
    }
    
    /**
     * @return Term
     */
    public function getTerm()
    {
        return $this->term;
    }
    
    /**
     * @param Term $term
     *
     * @return TermBlock
     */
    public function setTerm(Term $term)
    {
        $this->term = $term;
        
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
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return TermBlock
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
}