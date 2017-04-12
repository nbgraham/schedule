<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User.
 * 
 * @ORM\Entity()
 * @ORM\Table(name="user", indexes={
 *     @ORM\Index(name="idx_username", columns={"username"}),
 *     @ORM\Index(name="idx_email",    columns={"email"}),
 * })
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class User extends AbstractEntity implements UserInterface
{
    const SALT = "Adp2kd0f";
    
    /**
     * The Session instance.
     * 
     * @ORM\OneToMany(targetEntity="Session", mappedBy="user")
     * 
     * @var Session[]
     */
    protected $sessions;
    
    /**
     * The roles the user has.
     * 
     * @var Role[]
     */
    protected $roles;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * The username the user picked.
     *
     * @ORM\Column(type="string", length=50)
     */
    protected $username;

    /**
     * The users password - hopefully encrypted.
     *
     * @ORM\Column(type="string", length=125, nullable=false)
     * 
     * @var String
     */
    protected $password;

    /**
     * The users first name.
     *
     * @ORM\Column(type="string", length=35, nullable=true)
     * 
     * @var String
     */
    protected $first_name;

    /**
     * The user's last name.
     *
     * @ORM\Column(type="string", length=35, nullable=true)
     * 
     * @var String
     */
    protected $last_name;

    /**
     * The user's plain password. This value should not be stored.
     * 
     * @var String
     */
    protected $plain_password;

    /**
     * The users email.
     *
     * @ORM\Column(type="string", length=75)
     * 
     * @var String
     */
    protected $email;

    /**
     * The salt used to hash the user's password.
     * 
     * @ORM\Column(type="string", length=10)
     * 
     * @var String
     */
    protected $salt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sessions = new ArrayCollection();
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
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->getRoles()->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set the user salt. NOT used when generating a new salt
     * {@see User::replaceSalt}.
     * 
     * @param string $salt
     * 
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Set the custom user salt.
     * 
     * @param string $salt
     * 
     * @return User
     */
    public function replaceSalt($salt)
    {
        $salt       = base64_encode($salt);
        $this->salt = substr($salt, 0, 10);
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
        $this->plain_password = null;
    }

    /**
     * Fetch the plain password.
     * 
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plain_password;
    }

    /**
     * Sets the plain password.
     * 
     * @param string $password
     * 
     * @return $this
     */
    public function setPlainPassword($password)
    {
        $this->plain_password = $password;
        return $this;
    }

    /**
     * Gets the users full name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Sets the users full name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $parts = explode(' ', $name);
        
        return $this
            ->setFirstName(array_shift($parts))
            ->setLastName(end($parts))
        ;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Add session
     *
     * @param Session $session
     * @return User
     */
    public function addSession(Session $session)
    {
        $this->sessions[] = $session;
    
        return $this;
    }

    /**
     * Remove session
     *
     * @param Session $session
     */
    public function removeSession(Session $session)
    {
        $this->sessions->removeElement($session);
    }

    /**
     * Get sessions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSessions()
    {
        return $this->sessions;
    }
}