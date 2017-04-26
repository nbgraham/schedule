<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Redundant storage of user session data.
 * 
 * #ORM\Entity
 * #ORM\HasLifecycleCallbacks
 * #ORM\Table(name="session", indexes={
 *    #ORM\Index(name="idx_expire_time", columns={"expire_time"}),
 *    #ORM\Index(name="idx_last_active", columns={"last_active"})
 * })
 *
 * @author Austin Shinpaugh <ashinpaugh@ou.com>
 */
class Session extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sessions", cascade={"merge", "detach"})
     * @var User
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var String
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $expire_time;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $last_active;

    /**
     * @ORM\Column(type="text")
     * @var String
     */
    protected $data;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * Get the session Id.
     * 
     * @return Int
     */
    public function getId()
    {
        return $this->getId();
    }

    /**
     * @ORM\PrePersist
     */
    public function onBeforeStore()
    {
        $this->setDateCreated(new \DateTime());
    }
    
    /**
     * Set session_id
     *
     * @param string $sessionId
     * @return Session
     */
    public function setId($sessionId)
    {
        $this->id = $sessionId;
    
        return $this;
    }

    /**
     * Set date_created
     *
     * @param \DateTime $dateCreated
     * @return Session
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;
    
        return $this;
    }

    /**
     * Get date_created
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set expire_time
     *
     * @param \DateTime $expireTime
     * @return Session
     */
    public function setExpireTime($expireTime)
    {
        $this->expire_time = $expireTime;
    
        return $this;
    }

    /**
     * Get expire_time
     *
     * @return \DateTime 
     */
    public function getExpireTime()
    {
        return $this->expire_time;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return Session
     */
    public function setData($data)
    {
        $this->data = $data;
    
        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set user
     *
     * @param User $user
     * @return Session
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set last_active
     *
     * @param \DateTime $lastActive
     * @return Session
     */
    public function setLastActive($lastActive)
    {
        $this->last_active = $lastActive;
    
        return $this;
    }

    /**
     * Get last_active
     *
     * @return \DateTime 
     */
    public function getLastActive()
    {
        return $this->last_active;
    }
}