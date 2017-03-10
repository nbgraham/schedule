<?php

namespace ATS\Bundle\ScheduleBundle\Util\Parser;

use ATS\Bundle\ScheduleBundle\Entity\Building;
use ATS\Bundle\ScheduleBundle\Entity\Campus;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\Event;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Room;
use ATS\Bundle\ScheduleBundle\Entity\Term;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Console\Input\InputInterface;
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
     * @var resource
     */
    protected $handle;
    
    /**
     * @var InputInterface
     */
    protected $input;
    
    /**
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * BookParser constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    
    public function setIncludeOnline($on)
    {
        $this->include_online = $on;
        return $this;
    }
    
    public function doParse(OutputInterface $output = null)
    {
        $this->output = $output;
        
        $this->run();
    }
    
    protected function run()
    {
        $num_entries = count(file($this->path)) - 1;
        
        $this->openfile();
        
        while($data = fgetcsv($this->handle)) {
            $class = $this->parseline($data);
            
            if ($this->output instanceof outputinterface) {
                $this->printline($class);
            }
        }
        
        $this->getManager()->flush();
    }
    
    protected function parseLine(array $data)
    {
        $this->output->writeln(implode(' ', $data));
        
        $campus = $this->getCampus($data);
        $this->getBuilding($campus, $data);
        
        $instructor = $this->getInstructor($data);
        $term       = $this->getTerm($data);
        $course     = $this->getCourse($term, $data);
        
        return $this->parseClass($campus, $course, $term, $instructor, $data);
    }
    
    protected function getCampus(array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Campus');
        $object = $repo->findBy([
            'display_name' => $data[9]
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Campus($data[9]);
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getBuilding(Campus $campus, array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Building');
        $object = $repo->findBy([
            'name' => $data[18]
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Building($campus, $data[18]);
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getRoom(Building $parent, array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Room');
        $object = $repo->findBy([
            'id' => $data[19]
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Room($parent, $data[19]);
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getInstructor(array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Instructor');
        $object = $repo->findBy([
            'id' => $data[7]
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Instructor($data[7], $data[6]);
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function getTerm(array $data)
    {
        $term_meta = $this->parseTerm($data);
        $this->output->writeln(implode(' - ', $term_meta));
        $this->output->writeln(count($data));
        
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Term');
        $object = $repo->findBy([
            'year'     => $term_meta['year'],
            'semester' => $term_meta['semester'],
        ]);
        
        if ($object) {
            $this->validateTermBlock($object, $term_meta['block']);
            return $object;
        }
        
        $object = new Term($data[0], $term_meta['year'], $term_meta['semester']);
        $this->getManager()->persist($object);
        $this->validateTermBlock($object, $term_meta['block']);
        
        return $object;
    }
    
    protected function validateTermBlock(Term $term, $block)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:TermBlock');
        $object = $repo->findBy([
            'term' => $term,
            'name' => $block,
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new TermBlock($term, $block);
        $term->addBlock($object);
        
        $this->getManager()->persist($object);
    }
    
    protected function getCourse(Term $term, array $data)
    {
        $repo   = $this->getManager()->getRepository('ATSScheduleBundle:Course');
        $object = $repo->findBy([
            'number' => $data[2],
        ]);
        
        if ($object) {
            return $object;
        }
        
        $object = new Course();
        $object
            ->setNumber($data[2])
            ->setTitle($data[5])
            ->setTerm($term)
            ->setSubject($data[1])
            ->setLevel($data[28])
            ->setMaximumEnrollment($data[11])
        ;
        
        $this->getManager()->persist($object);
        
        return $object;
    }
    
    protected function parseClass(Campus $campus, Course $course, Term $term, Instructor $instructor, array $data)
    {
        $event = new Event();
        $event
            ->setCrn($data[4])
            ->setDays($data[20])
            ->setStartTime($data[21])
            ->setEndTime($data[22])
            ->setStatus($data[8])
            ->setNumEnrolled($data[12])
            ->setSection($data[3])
            ->setCampus($campus)
            ->setCourse($course)
            ->setTerm($term)
            ->setInstructor($instructor)
        ;
        
        $this->getManager()->persist($event);
        
        return $event;
    }
    
    private function parseTerm(array $data)
    {
        $parts = explode(' ', $data[0]);
        return [
            'year'     => end($parts),
            'semester' => $parts[0],
            'block'    => $data[27],
        ];
    }
    
    protected function printLine(Event $class)
    {
        
    }
    
    protected function openFile()
    {
        if (!$this->handle = fopen($this->path, 'r')) {
            throw new FileNotFoundException();
        }
        
        // Ignore the column headers.
        fgetcsv($this->handle);
        
        return $this;
    }
    
    
    private function getManager()
    {
        return $this->doctrine->getManager();
    }
}