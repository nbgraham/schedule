<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;

use ATS\Bundle\ScheduleBundle\Entity\Building;
use ATS\Bundle\ScheduleBundle\Entity\Campus;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Room;
use ATS\Bundle\ScheduleBundle\Entity\Section;
use ATS\Bundle\ScheduleBundle\Entity\Subject;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use ATS\Bundle\ScheduleBundle\Util\Helper\ImportDriverHelper;
use Doctrine\Bundle\DoctrineBundle\Registry;

abstract class AbstractImportDriver
{
    /**
     * @var Registry
     */
    private $doctrine;
    
    /**
     * @var Boolean
     */
    private $online;
    
    /**
     * The entries we plan on importing.
     * 
     * @var String[]
     */
    private $entries;
    
    /**
     * @var String
     */
    protected $location;
    
    /**
     * @var Integer
     */
    protected $num_rows;
    
    /**
     * @var Integer
     */
    protected $page;
    
    /**
     * @var Integer
     */
    protected $limit;
    
    /**
     * @var ImportDriverHelper
     */
    protected $helper;
    
    /**
     * AbstractImportDriver constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, ImportDriverHelper $helper)
    {
        $this->doctrine = $doctrine;
        $this->helper   = $helper;
        
        $this->entries = [];
        $this->data    = [];
        $this->online  = false;
        $this->location = null;
        
        $this->disableDoctrineLogging();
    }
    
    public abstract function getCount();
    
    /**
     * Set the entries.
     *
     * @param null $mixed
     *
     * @return $this
     *
     */
    protected abstract function loadRawData($mixed = null);
    
    /**
     * Initialize the import settings.
     * 
     * Should probably be called in the service declaration.
     * 
     * @param mixed $mixed
     * 
     * @return void
     */
    public abstract function init($mixed = null);
    
    /**
     * Create a campus object.
     * 
     * @return Campus
     */
    public abstract function createCampus();
    
    /**
     * Create a building object.
     * 
     * @param Campus $campus The campus object the building belongs too.
     * 
     * @return Building
     */
    public abstract function createBuilding(Campus $campus = null);
    
    /**
     * Create a room object.
     * 
     * @return Room
     */
    public abstract function createRoom(Building $building = null);
    
    /**
     * Create an instructor object.
     * 
     * @return Instructor
     */
    public abstract function createInstructor();
    
    /**
     * Create a term and term block objects.
     * 
     * @return TermBlock
     */
    public abstract function createTerm();
    
    /**
     * Create a subject object.
     * 
     * @return Subject
     */
    public abstract function createSubject();
    
    /**
     * Create a course object.
     * 
     * @param Subject $subject
     * 
     * @return Course
     */
    public abstract function createCourse(Subject $subject = null);
    
    /**
     * Create a section object.
     * 
     * @param Subject $subject
     * 
     * @return Section
     */
    public abstract function createSection(Subject $subject = null);
    
    /**
     * Parse special cases of the building codes.
     * 
     * @param array $data
     *
     * @return array
     */
    protected abstract function parseBuilding();
    
    /**
     * Get the location.
     * 
     * @param string $type
     *
     * @return array|mixed|null|String
     */
    protected function getLocation($type = '')
    {
        if (!$this->location) {
            $this->location = $this->parseBuilding();
        }
        
        return $type ? $this->location[$type] : $this->location;
    }
    
    
    public function nextEntry()
    {
        $this->location = null;
        
        return next($this->entries);
    }
    
    public function prevEntry()
    {
        $this->location = null;
        
        return prev($this->entries);
    }
    
    public function firstEntry()
    {
        $this->location = null;
        
        return reset($this->entries);
    }
    
    public function getEntry($index = null)
    {
        $value = current($this->entries);
        
        if ($index) {
            return $value[$index];
        }
        
        return $value;
    }
    
    /**
     * @return array|\String[]
     */
    public function getEntries()
    {
        return $this->entries;
    }
    
    /**
     * @param array $entries
     *
     * @return $this
     */
    public function setEntries(array $entries)
    {
        $this->entries = $entries;
        
        return $this;
    }
    
    /**
     * Sets the root directory.
     * 
     * @param string $dir
     *
     * @return $this
     */
    public function setRootDir($dir)
    {
        $this->root_dir = $dir;
        
        return $this;
    }
    
    /**
     * Whether to include online courses or not.
     * 
     * @param boolean $flag
     *
     * @return $this
     */
    public function setIncludeOnline($flag)
    {
        $this->online = $flag;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function getIncludeOnline()
    {
        return $this->online;
    }
    
    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }
    
    /**
     * Format the date string.
     * 
     * @param string $date
     *
     * @return \DateTime
     */
    protected function getDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date;
        }
        
        return new \DateTime($date);
    }
    
    /**
     * Set environment variables.
     * 
     * @return $this
     */
    protected function setEnvironmentVars()
    {
        // Make sure that OSX line endings are accounted for when parsing the CSV.
        ini_set('auto_detect_line_endings',true);
        
        return $this;
    }
    
    /**
     * Disable doctrine's logger.
     * 
     * @return $this
     */
    protected function disableDoctrineLogging()
    {
        $this->doctrine
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null)
        ;
        
        return $this;
    }
}