<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;


use ATS\Bundle\ScheduleBundle\Entity\Building;
use ATS\Bundle\ScheduleBundle\Entity\Campus;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Room;
use ATS\Bundle\ScheduleBundle\Entity\Section;
use ATS\Bundle\ScheduleBundle\Entity\Subject;
use ATS\Bundle\ScheduleBundle\Entity\Term;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class BookImportDriver extends AbstractImportDriver
{
    const CSV_PATH = 'datastores/Classes.csv';
    
    protected $path;
    
    public function __construct(Registry $doctrine)
    {
        parent::__construct($doctrine);
    }
    
    /**
     * {@inheritdoc}
     */
    public function init($mixed = null)
    {
        $this
            ->setEnvironmentVars()
            ->setPath($mixed ?: static::CSV_PATH)
            ->loadRawData()
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createCampus()
    {
        return new Campus($this->getEntry(9));
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilding(Campus $campus = null)
    {
        $campus   = $campus ?: $this->createCampus();
        $building = new Building(
            $campus,
            $this->getLocation('building')
        );
        
        return $building;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createRoom(Building $building = null)
    {
        $building = $building ?: $this->createBuilding();
        $number   = $this->getLocation('room') ?: '0000';
        $room     = new Room(
            $building,
            $number
        );
        
        return $room;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createInstructor()
    {
        $data = $this->getEntry();
        $id   = (int) $data[7];
        $name = $data[7] ? $data[6] : 'N/A';
        
        return new Instructor($id, $name);
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function createTerm()
    {
        $entry = $this->getEntry();
        $dict  = $this->parseTerm($entry);
        $term  = new Term($entry[0], $dict['year'], $dict['semester']);
        $block = new TermBlock($term, $dict['block']);
        
        $term->addBlock($block);
        
        return $block;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new Subject($this->getEntry(1));
    }
    
    /**
     * {@inheritdoc}
     */
    public function createCourse(Subject $subject = null)
    {
        $entry   = $this->getEntry();
        $subject = $subject ?: $this->createSubject();
        $course  = new Course($subject, $entry[2]);
        
        $course
            ->setName($entry[5])
            ->setLevel($entry[36])
        ;
        
        return $course;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createSection(Subject $subject = null)
    {
        $entry   = $this->getEntry();
        $section = new Section();
        
        $section
            ->setCrn($entry[4])
            ->setDays($entry[20])
            ->setStartDate($this->getDate($entry[16]))
            ->setEndDate($this->getDate($entry[17]))
            ->setStartTime($entry[21])
            ->setEndTime($entry[22])
            ->setStatus($entry[8])
            ->setSection($entry[3])
            ->setNumEnrolled($entry[12])
            ->setMaximumEnrollment($entry[11])
            /*->setSubject($subject)
            ->setCourse($course)
            ->setCampus($this->createCampus())
            ->setBlock($this->createTerm())
            ->setInstructor($this->createInstructor())
            ->setRoom($this->createRoom())*/
        ;
        
        return $section;
    }
    
    /**
     * Break the terms into parts.
     *
     * @param array $data
     *
     * @return array
     */
    protected function parseTerm(array $data)
    {
        $parts = explode(' ', $data[0]);
        return [
            'year'     => end($parts),
            'semester' => $parts[0],
            'block'    => $data[35],
        ];
    }
    
    /**
     * Set the path for the file to import.
     * 
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        
        return $this;
    }
    
    /**
     * Read the CSV contents line by line and read the valid entries into memory.
     * 
     * @return $this
     */
    protected function loadRawData()
    {
        $handle = $this->openFile();
        $data   = [];
        
        while($line = fgetcsv($handle)) {
            if (!$this->isValidEntry($line)) {
                continue;
            }
            
            $data[] = $line;
        }
        
        fclose($handle);
        
        return $this->setEntries($data);
    }
    
    /**
     * Open a file for reading.
     * 
     * @return bool|resource
     */
    protected function openFile()
    {
        if (!$handle = fopen($this->path, 'r')) {
            throw new FileNotFoundException();
        }
        
        // Ignore the column headers.
        fgetcsv($handle);
        
        return $handle;
    }
    
    /**
     * Determine if we should parse a row.
     * 
     * @param array $data
     *
     * @return bool
     */
    private function isValidEntry(array $data)
    {
        // 0 = semester - invalid entry. 8 = status.
        if ('...' === $data[0] || '...' === $data[8]) {
            return false;
        }
        
        if (!$this->getIncludeOnline() && $this->isOnline($data)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Determine if the class offered is an online class.
     * 
     * @param array $data
     *
     * @return bool
     */
    private function isOnline(array $data)
    {
        // $this->getDate($data[16]) <= new \DateTime()
        
        // 18 = Building. 19 = Room. 20 = Days.
        return !$data[18] || null === $data[19] || null === $data[20];
    }
    
    /**
     * Parse special cases of the building codes.
     * 
     * @param array $data
     *
     * @return array
     */
    protected function parseBuilding()
    {
        $data = $this->getEntry();
        
        if ('XCH' !== substr($data[18], 0, 3)) {
            return [
                'building' => $data[18],
                'room'     => $data[19],
            ];
        }
        
        return [
            'building' => 'XCH',
            'room'     => substr($data[18], 3),
        ];
    }
}