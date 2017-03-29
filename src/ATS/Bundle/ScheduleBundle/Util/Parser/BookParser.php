<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;

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
     * 
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
    }
    
    protected function parseLine(array $data)
    {
        // 0 = semester - invalid entry. 20 = Days.
        if ('...' === $data[0] || (!$this->include_online && $this->isOnline($data))) {
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
        
        $class = $this->parseClass($data, $campus, $course, $term_block, $instructor, $room);
        
        // Each class needs real IDs to reference, so if any of these IDs aren't already stored in the DB - store them.
        if (!$campus->getId() || !$course->getId() || !$term->getId() || !$instructor->getId() || !$room->getId()) {
            $this->getManager()->flush();
        }
        
        return $class;
    }
    
    protected function getCampus(array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Campus');
        $object = $repo->findOneBy([
            'name' => $data[9]
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Campus($data[9]);
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getBuilding(Campus $campus, array $location)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Building');
        $object = $repo->findOneBy([
            'name'   => $location['building'],
            'campus' => $campus,
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Building($campus, $location['building']);
        $campus->addBuilding($object);
        
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getRoom(Building $building, array $location)
    {
        $name   = $location['room'] ?: '0000';
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Room');
        $object = $repo->findOneBy([
            'number'   => $name,
            'building' => $building,
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Room($building, $name);
        $building->addRoom($object);
        
        $this->getManager()->persist($object);
        
        return $object;
    }
    
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
    
    protected function getInstructor(array $data)
    {
        $id   = (int) $data[7];
        $name = $data[7] ? $data[6] : 'N/A'; 
        
        if (($object = $this->find('ATSScheduleBundle:Instructor', $id)) instanceof Instructor) {
            return $object;
        }
        
        $object = new Instructor($id, $name);
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getTerm(array $data, TermBlock &$block = null)
    {
        $term   = $this->parseTerm($data);
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Term');
        $object = $repo->findOneBy([
            'year'     => $term['year'],
            'semester' => $term['semester'],
        ]);
        
        if ($object) {
            $block = $this->validateTermBlock($object, $term['block']);
            return $object;
        }
        
        $object = new Term($data[0], $term['year'], $term['semester']);
        $block  = $this->validateTermBlock($object, $term['block']);
        
        return $object;
    }
    
    protected function validateTermBlock(Term $term, $block)
    {
        if (!$term->getId()) {
            return $this->createBlock($term, $block);
        }
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:TermBlock');
        $object = $repo->findOneBy([
            'term' => $term,
            'name' => $block,
        ]);
        
        if ($object) {
            return $object;
        }
        
        return $this->createBlock($term, $block);
    }
    
    private function createBlock(Term $term, $block)
    {
        $object = new TermBlock($term, $block);
        $term->addBlock($object);
        
        $this->getManager()->persist($object);
        $this->getManager()->flush();
        
        return $object;
    }
    
    protected function getCourse(array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Course');
        $object = $repo->findOneBy([
            'subject' => $data[1],
            'number'  => $data[2],
        ]);
        
        if ($object instanceof Course) {
            return $object;
        }
        
        $object = new Course($data[1], $data[2]);
        $object
            ->setTitle($data[5])
            ->setLevel($data[36])
            ->setMaximumEnrollment($data[11])
        ;
        
        $this->getManager()->persist($object);
        
        return $object;
    }
    
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
     * @return object
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
}