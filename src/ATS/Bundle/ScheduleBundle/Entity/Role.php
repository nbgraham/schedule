<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * A Node role. A collection of roles help determine user's access
 * to features and permissions.
 * 
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @ORM\Cache("READ_ONLY")
 */
class Role extends AbstractEntity implements RoleInterface
{
    /**
     * The Role unique ID.
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * The 'Symfony style' role name.
     * I.E. ROLE_ADMIN
     * 
     * @ORM\Column(type="string", length=20)
     * @var String
     */
    protected $role;

    /**
     * The front end display name for the role.
     * I.E. Node Administrator
     * 
     * @ORM\Column(type="string", length=75)
     * @var String
     */
    protected $name;
    
    /**
     * The Role priority.
     * 
     * @ORM\Column(type="smallint")
     */
    protected $priority;
    
    /**
     * Constructor
     *
     * @param string $role     The symfony-style role name.
     * @param string $name     The usr readable role.
     * @param int    $priority Determines which role is has priorities over others.
     */
    public function __construct($role, $name, $priority)
    {
        $this
            ->setRole($role)
            ->setName($name)
            ->setPriority($priority)
        ;
    }
    
    /**
     * Symfony-style role name.
     * 
     * @return String
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return (int) $this->id;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get user readable name.
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Role
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    
        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }
    
    /**
     * Set role
     *
     * @param string $role
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }
}