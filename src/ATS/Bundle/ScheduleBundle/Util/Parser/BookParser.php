<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;

use ATS\Bundle\ScheduleBundle\Entity\AbstractEntity;
use ATS\Bundle\ScheduleBundle\Entity\Building;
use ATS\Bundle\ScheduleBundle\Entity\Campus;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\ClassEvent;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Room;
use ATS\Bundle\ScheduleBundle\Entity\Term;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Parses the Book CSV file.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class BookParser
{
    /**
     * @var Registry 
     */
    protected $doctrine;
    
    /**
     * @var string
     */
    protected $path;
    
    /**
     * @var bool
     */
    protected $include_online;
    
    /**
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * BookParser constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        
        $this->setIncludeOnline(false);
    }
    
    /**
     * Start the parsing.
     * 
     * @param OutputInterface|null $output
     */
    public function doParse(OutputInterface $output = null)
    {
        $this->output = $output;
        
        $this->run();
    }
    
    /**
     * Perform the import.
     */
    protected function run()
    {
        $this->disableDoctrineLogging();
        
        $handle   = $this->openFile();
        $progress = new ProgressBar($this->output, count(file($this->path)));
        $progress->setFormat('debug');
        $progress->start();
        
        $i      = 1;
        $chunks = 100;
        while($data = fgetcsv($handle)) {
            $this->parseline($data);
            
            if ($i % $chunks == 0) {
                $this->getManager()->flush();
                
                $progress->advance($chunks);
            }
            
            $i++;
        }
        
        $this->getManager()->flush();
        $progress->finish();
        
        fclose($handle);
        
        // The Progress Bar doesn't clear the line upon completion.
        if ($this->output instanceof OutputInterface) {
            $this->output->writeln('');
        }
    }
    
    /**
     * Parse a line from The Book.
     * 
     * @param array $data
     *
     * @return null|ClassEvent
     */
    protected function parseLine(array $data)
    {
        // 0 = semester - invalid entry.
        if ('...' === $data[0]) {
            return null;
        }
        
        if (!$this->include_online && $this->isOnline($data)) {
            return null;
        }
        
        $room     = null;
        $location = $this->parseBuilding($data);
        $campus   = $this->getCampus($data);
        if ($building = $this->getBuilding($campus, $location)) {
            $room = $this->getRoom($building, $location);
        }
        
        $instructor = $this->getInstructor($data);
        $term       = $this->getTerm($data, $term_block);
        $course     = $this->getCourse($data);
        
        return $this->parseClass($data, $campus, $course, $term_block, $instructor, $room);
    }
    
    /**
     * Get the Campus.
     * 
     * @param array $data
     *
     * @return AbstractEntity|Campus
     */
    protected function getCampus(array $data)
    {
        static $instances;
        
        $value = $data[9];
        $key   = $this->getKey(['name' => $value]);
        
        if ($object = $this->getStored($instances, $key)) {
            return $object;
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Campus');
        $object = $repo->findOneBy([
            'name' => $value
        ]);
        
        if ($object instanceof Campus) {
            return ($instances[$key] = $object);
        }
        
        $object = new Campus($value);
        $this->getManager()->persist($object);
        
        $instances[$key] = $object;
        
        return $object;
    }
    
    /**
     * Get the building.
     * 
     * @param Campus $campus
     * @param array  $location
     *
     * @return AbstractEntity|Building
     */
    protected function getBuilding(Campus $campus, array $location)
    {
        static $instances;
        
        $key = $this->getKey(['campus' => $campus->getName(), 'name' => $location['building']]);
        
        if ($object = $this->getStored($instances, $key)) {
            return $object;
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Building');
        $object = $repo->findOneBy([
            'name'   => $location['building'],
            'campus' => $campus,
        ]);
        
        if ($object instanceof Building) {
            return ($instances[$key] = $object);
        }
        
        $object = new Building($campus, $location['building']);
        $campus->addBuilding($object);
        
        $this->getManager()->persist($object);
        
        $instances[$key] = $object;
        
        return $object;
    }
    
    /**
     * Get the room.
     * 
     * @param Building $building
     * @param array    $location
     *
     * @return AbstractEntity|Room
     */
    protected function getRoom(Building $building, array $location)
    {
        static $instances;
        
        $name = $location['room'] ?: '0000';
        $key  = $this->getKey(['building' => $building->getName(),'name' => $name]);
        
        if ($object = $this->getStored($instances, $key)) {
            return $object;
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Room');
        $object = $repo->findOneBy([
            'number'   => $name,
            'building' => $building,
        ]);
        
        if ($object instanceof Room) {
            return ($instances[$key] = $object);
        }
        
        $object = new Room($building, $name);
        $building->addRoom($object);
        
        $this->getManager()->persist($object);
        
        $instances[$key] = $object;
        
        return $object;
    }
    
    /**
     * Get the instructor.
     * 
     * @param array $data
     *
     * @return AbstractEntity|Instructor|object
     */
    protected function getInstructor(array $data)
    {
        static $instances;
        
        $id   = (int) $data[7];
        $name = $data[7] ? $data[6] : 'N/A';
        $key  = $this->getKey(['id' => $id, 'name' => $name]);
        
        if ($object = $this->getStored($instances, $key)) {
            return $object;
        }
        
        if (($object = $this->find('ATSScheduleBundle:Instructor', $id)) instanceof Instructor) {
            return ($instances[$key] = $object);
        }
        
        $object = new Instructor($id, $name);
        $this->getManager()->persist($object);
        
        $instances[$key] = $object;
        
        return $object;
    }
    
    /**
     * Get the term.
     * 
     * @param array          $data
     * @param TermBlock|null $block
     *
     * @return AbstractEntity|Term
     */
    protected function getTerm(array $data, TermBlock &$block = null)
    {
        static $instances;
        
        $term = $this->parseTerm($data);
        $key  = $this->getKey($term);
        
        if ($object = $this->getStored($instances, $key)) {
            $block = $this->validateTermBlock($object, $term['block']);
            return $object;
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Term');
        $object = $repo->findOneBy([
            'year'     => $term['year'],
            'semester' => $term['semester'],
        ]);
        
        if ($object instanceof Term) {
            $block = $this->validateTermBlock($object, $term['block']);
            return ($instances[$key] = $object);
        }
        
        $object = new Term($data[0], $term['year'], $term['semester']);
        $block  = $this->validateTermBlock($object, $term['block']);
        
        $instances[$key] = $object;
        
        return $object;
    }
    
    /**
     * Get the course.
     * 
     * @param array $data
     *
     * @return AbstractEntity|Course|null
     */
    protected function getCourse(array $data)
    {
        static $instances;
        
        $subject = $data[1];
        $number  = $data[2];
        $key     = $this->getKey(['subject' => $subject, 'number' => $number]);
        
        if ($object = $this->getStored($instances, $key)) {
            return $object;
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Course');
        $object = $repo->findOneBy([
            'subject' => $subject,
            'number'  => $number,
        ]);
        
        if ($object instanceof Course) {
            return ($instances[$key] = $object);
        }
        
        $object = new Course($subject, $number);
        $object
            ->setTitle($data[5])
            ->setLevel($data[36])
            ->setMaximumEnrollment($data[11])
        ;
        
        $this->getManager()->persist($object);
        
        $instances[$key] = $object;
        
        return $object;
    }
    
    /**
     * Import a class.
     * 
     * @param array      $data
     * @param Campus     $campus
     * @param Course     $course
     * @param TermBlock  $block
     * @param Instructor $instructor
     * @param Room|null  $room
     *
     * @return AbstractEntity|ClassEvent|object
     */
    protected function parseClass(array $data, Campus $campus, Course $course, TermBlock $block, Instructor $instructor, Room $room = null)
    {
        if (($object = $this->find('ATSScheduleBundle:ClassEvent', $data[4])) instanceof ClassEvent) {
            return $object;
        }
        
        $event = new ClassEvent();
        $event
            ->setCrn($data[4])
            ->setDays($data[20])
            ->setStartDate($this->getDate($data[16]))
            ->setEndDate($this->getDate($data[17]))
            ->setStartTime($data[21])
            ->setEndTime($data[22])
            ->setStatus($data[8])
            ->setNumEnrolled($data[12])
            ->setSection($data[3])
            ->setCampus($campus)
            ->setCourse($course)
            ->setBlock($block)
            ->setInstructor($instructor)
            ->setRoom($room)
        ;
        
        $this->getManager()->persist($event);
        
        return $event;
    }
    
    /**
     * Determine if the class offered is an online class.
     * 
     * @param array $data
     *
     * @return bool
     */
    protected function isOnline(array $data)
    {
        // 20 = Days.
        return null === $data[20]
            && $this->getDate($data[16]) <= new \DateTime()
        ;
    }
    
    /**
     * Parse special cases of the building codes.
     * 
     * @param array $data
     *
     * @return array
     */
    private function parseBuilding(array $data)
    {
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
    
    /**
     * Get the Term Block.
     *
     * @param AbstractEntity|Term $term
     * @param string              $block
     *
     * @return TermBlock|null|object
     */
    protected function validateTermBlock(AbstractEntity $term, $block)
    {
        static $instances;
        
        $key = $this->getKey(['term' => $term->getDisplayName(), 'name' => $block]);
        if ($object = $this->getStored($instances, $key)) {
            return $object;
        }
        
        if (!$term->getId()) {
            return ($instances[$key] = $this->createBlock($term, $block));
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:TermBlock');
        $object = $repo->findOneBy([
            'term' => $term,
            'name' => $block,
        ]);
        
        if ($object instanceof TermBlock) {
            return ($instances[$key] = $object);
        }
        
        $instances[$key] = ($object = $this->createBlock($term, $block));
        
        return $object;
    }
    
    /**
     * Create a term block.
     * 
     * @param Term   $term
     * @param string $block
     *
     * @return TermBlock
     */
    private function createBlock(Term $term, $block)
    {
        $object = new TermBlock($term, $block);
        $term->addBlock($object);
        
        $this->getManager()->persist($object);
        $this->getManager()->flush();
        
        return $object;
    }
    
    /**
     * Break the terms into parts.
     * 
     * @param array $data
     *
     * @return array
     */
    private function parseTerm(array $data)
    {
        $parts = explode(' ', $data[0]);
        return [
            'year'     => end($parts),
            'semester' => $parts[0],
            'block'    => $data[35],
        ];
    }
    
    /**
     * Opens a CSV file for read only access.
     * 
     * @return resource
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
     * Tiny wrapper around doctrine Registry.
     * 
     * @param string $className
     * @param mixed  $id
     *
     * @return AbstractEntity|object|null
     */
    private function find($className, $id)
    {
        return $this->doctrine->getManager()
            ->find($className, $id)
        ;
    }
    
    /**
     * Format the date string.
     * 
     * @param string $date
     *
     * @return \DateTime
     */
    private function getDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date;
        }
        
        return new \DateTime($date);
    }
    
    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    private function getManager()
    {
        return $this->doctrine->getManager();
    }
    
    /**
     * Sets the path to the csv to parse.
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
     * Sets the flag for including online classes.
     * 
     * @param boolean $on
     *
     * @return $this
     */
    public function setIncludeOnline($on)
    {
        $this->include_online = $on;
        return $this;
    }
    
    /**
     * Save memory by disabling sql query logging.
     */
    private function disableDoctrineLogging()
    {
        $this->doctrine
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null)
        ;
    }
    
    /**
     * Generate a standardized key used for in memory storage.
     * 
     * @param array $parts
     *
     * @return string
     */
    protected function getKey(array $parts)
    {
        return implode('-', $parts);
    }
    
    /**
     * Check the local memory cache for an instance for a desired object.
     * 
     * @param array  $instances
     * @param string $key
     *
     * @return null|AbstractEntity
     */
    protected function getStored(&$instances, $key)
    {
        if (!$instances) {
            $instances = [];
            return null;
        }
        
        if (array_key_exists($key, $instances)) {
            return $instances[$key];
        }
        
        return null;
    }
}