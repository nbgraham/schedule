<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
     * @Serializer\Exclude()
     * 
     * @ORM\OneToMany(targetEntity="Section", mappedBy="block", fetch="EXTRA_LAZY")
     * @var Section[]
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
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'term' => $this->getTerm()->getName(),
            'name' => $this->name,
        ];
    }
    
    /**
     * @Serializer\VirtualProperty()
     */
    public function getDisplayName()
    {
        switch ($this->getName()) {
            case 1:
                return 'Full Semester';
            case 2:
                return 'Module 1 (1st Half)';
            case 3:
                return 'Module 2 (2nd Half)';
            case 'DEC':
                return 'December';
            case 'NCE':
                return 'Norman Contract Enrollment';
            case 'JNX':
                return 'JANIX credit';
            case 'L01':
                return 'Liberal Studies 1';
            case 'L02':
                return 'Liberal Studies 2';
            case 'L03':
                return 'Liberal Studies 3';
            default:
                return $this->getName();
        }
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