<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;

use ATS\Bundle\ScheduleBundle\Entity\AbstractEntity;
use ATS\Bundle\ScheduleBundle\Entity\Building;
use ATS\Bundle\ScheduleBundle\Entity\Campus;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\ClassEvent;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Room;
use ATS\Bundle\ScheduleBundle\Entity\Subject;
use ATS\Bundle\ScheduleBundle\Entity\Term;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
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
        $subject  = $this->getSubject($data);
        $location = $this->parseBuilding($data);
        $campus   = $this->getCampus($data);
        if ($building = $this->getBuilding($campus, $location)) {
            $room = $this->getRoom($building, $location);
        }
        
        $instructor = $this->getInstructor($data);
        $term       = $this->getTerm($data, $term_block);
        $course     = $this->getCourse($subject, $data);
        
        if (!$course instanceof Course) {
            return null;
        }
        
        return $this->parseClass($data, $campus, $subject, $course, $term_block, $instructor, $room);
    }
    
    protected function getSubject(array $data)
    {
        static $instances;
        
        $subject = $data[1];
        $key     = $this->getKey(['name' => $subject]);
        $repo    = $this->getManager()->getRepository('ATSScheduleBundle:Subject');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        return $this->persist($instances, $key, new Subject($subject));
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
        $repo  = $this->getManager()->getRepository('ATSScheduleBundle:Campus');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        return $this->persist($instances, $key, new Campus($value));
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
        
        $key  = $this->getKey(['name' => $location['building'], 'campus' => $campus->getName()]);
        $repo = $this->getManager()->getRepository('ATSScheduleBundle:Building');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        $object = new Building($campus, $location['building']);
        $campus->addBuilding($object);
        
        return $this->persist($instances, $key, $object);
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
        $key  = $this->getKey(['number' => $name, 'building' => $building->getName()]);
        $repo = $this->getManager()->getRepository('ATSScheduleBundle:Room');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        //echo "\nkeyy: " . $key . "\n";
        //echo "\nkey:" . array_key_exists($key, $instances) . "\n";
        //var_dump($instances);
        
        $object = new Room($building, $name);
        $building->addRoom($object);
        
        return $this->persist($instances, $key, $object);
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
        $repo = $this->getManager()->getRepository('ATSScheduleBundle:Instructor');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        /*if (($object = $this->find('ATSScheduleBundle:Instructor', $id)) instanceof Instructor) {
            return ($instances[$key] = $object);
        }*/
        
        return $this->persist($instances, $key, new Instructor($id, $name));
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
        $key  = $this->getKey(['year' => $term['year'], 'semester' => $term['semester']]);
        $repo = $this->getManager()->getRepository('ATSScheduleBundle:Term');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            $block = $this->validateTermBlock($object, $term['block']);
            return $object;
        }
        
        $object = new Term($data[0], $term['year'], $term['semester']);
        $block  = $this->validateTermBlock($object, $term['block']);
        
        return $this->persist($instances, $key, $object);
    }
    
    /**
     * Get the course.
     * 
     * @param array $data
     *
     * @return AbstractEntity|Course|null
     */
    protected function getCourse(Subject $subject, array $data)
    {
        static $instances;
        
        //$subject = $data[1];
        $number  = $data[2];
        $key     = $this->getKey(['subject' => $subject->getName(), 'number' => $number]);
        $repo    = $this->getManager()->getRepository('ATSScheduleBundle:Course');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        $object = new Course($subject, $number);
        $object
            ->setTitle($data[5])
            ->setLevel($data[36])
            ->setMaximumEnrollment($data[11])
        ;
        
        // Binary data was present in one of the fields.
        if (!mb_check_encoding($object->getTitle(), 'UTF-8')) {
            return null;
        }
        
        return $this->persist($instances, $key, $object);
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
    protected function parseClass(array $data, Campus $campus, Subject $subject, Course $course, TermBlock $block, Instructor $instructor, Room $room = null)
    {
        static $instances;
        
        $key   = $this->getKey(['crn' => $data[4]]);
        $repo  = $this->getManager()->getRepository('ATSScheduleBundle:ClassEvent');
        $event = $this->getStored($instances, $key, $repo) ?: new ClassEvent();
        
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
            ->setSubject($subject)
        ;
        
        return $this->persist($instances, $key, $event);
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
        
        $key  = $this->getKey(['term' => $term->getName(), 'name' => $block]);
        $repo = $this->getManager()->getRepository('ATSScheduleBundle:TermBlock');
        
        if ($object = $this->getStored($instances, $key, $repo)) {
            return $object;
        }
        
        if (!$term->getId()) {
            return ($instances[$key] = $this->createBlock($term, $block));
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
        ksort($parts);
        
        return implode('-', $parts);
    }
    
    /**
     * Check the local memory cache for an instance for a desired object.
     * 
     * @param array            $instances
     * @param string           $key
     * @param ObjectRepository $repo
     *
     * @return null|AbstractEntity
     */
    protected function getStored(&$instances, $key, $repo)
    {
        if (null !== $instances) {
            if (array_key_exists($key, $instances)) {
                return $instances[$key];
            }
            
            return null;
        }
        
        $instances = [];
        $stored    = $repo->findAll();
        
        /* @var AbstractEntity $item */
        foreach ($stored as $item) {
            $instances[$this->getKey($item->getKey())] = $item;
        }
        
        return $this->getStored($instances, $key, $repo);
    }
    
    protected function persist(array &$instances, $key, $object)
    {
        $this->getManager()->persist($object);
        
        return ($instances[$key] = $object);
    }
}