<?php

namespace ATS\Bundle\ScheduleBundle\DataFixtures\ORM;

use ATS\Bundle\ScheduleBundle\Entity\AbstractEntity;
use ATS\Bundle\ScheduleBundle\Entity\Building;
use ATS\Bundle\ScheduleBundle\Entity\Campus;
use ATS\Bundle\ScheduleBundle\Entity\Course;
use ATS\Bundle\ScheduleBundle\Entity\Instructor;
use ATS\Bundle\ScheduleBundle\Entity\Room;
use ATS\Bundle\ScheduleBundle\Entity\Section;
use ATS\Bundle\ScheduleBundle\Entity\Subject;
use ATS\Bundle\ScheduleBundle\Entity\Term;
use ATS\Bundle\ScheduleBundle\Entity\TermBlock;
use ATS\Bundle\ScheduleBundle\Util\Parser\AbstractImportDriver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractDataFixture extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var String[]
     */
    protected $location;
    
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    abstract public function load(ObjectManager $manager);
    
    /**
     * The lower the number, the sooner that this fixture is loaded.
     * 
     * @return int
     */
    abstract public function getOrder();
    
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * Get the import utility.
     * 
     * @param boolean $reset Reset the import entries's array pointer.
     * 
     * @return AbstractImportDriver
     */
    protected function getImporter($reset = false)
    {
        $importer = $this->container->get('schedule.book_import');
        
        if ($reset) {
            $importer->firstEntry();
        }
        
        return $importer;
    }
    
    /**
     * Return a campus object.
     * 
     * @return AbstractEntity|Campus
     */
    protected function getCampus()
    {
        $campus = $this->getImporter()->createCampus();
        $key    = $this->getKey($campus);
        
        if ($obj = $this->getReference($key)) {
            return $obj;
        }
        
        //echo "[Campus - Missed]: {$campus->getName()}\n";
        
        return $this->store($campus, $key);
    }
    
    /**
     * Returns an instance of the building object.
     * 
     * @return AbstractEntity|Building
     */
    protected function getBuilding(Campus $campus = null)
    {
        $campus   = $campus ?: $this->getCampus();
        $building = $this->getImporter()->createBuilding($campus);
        $key      = $this->getKey($building);
        
        if ($obj = $this->getReference($key)) {
            //echo "[Building - Found]: {$obj->getName()}\n";
            return $obj;
        }
        
        //echo "[Building - Missed]: {$building->getName()}\n";
        
        $campus->addBuilding($building);
        
        return $this->store($building, $key);
    }
    
    /**
     * Get a room object.
     * 
     * @param Building|null $building
     *
     * @return AbstractEntity|Room
     */
    protected function getRoom(Building $building = null)
    {
        $building = $building ?: $this->getBuilding();
        $room     = $this->getImporter()->createRoom($building);
        $room_key = $this->getKey($room);
        
        if ($obj = $this->getReference($room_key)) {
            //echo "Found cache key: $room_key\n";
            return $obj;
        }
        
        $building->addRoom($room);
        
        //echo "[Room - Missed]: {$building->getName()} - {$room->getNumber()}\n";
        
        return $this->store($room, $room_key);
    }
    
    /**
     * Return an instructor object.
     * 
     * @return AbstractEntity|Instructor
     */
    protected function getInstructor()
    {
        $instructor = $this->getImporter()->createInstructor();
        $key        = $this->getKey($instructor);
        
        if ($obj = $this->getReference($key)) {
            return $obj;
        }
        
        return $this->store($instructor, $key);
    }
    
    /**
     * Fetch the term block.
     * 
     * @return AbstractEntity|TermBlock
     */
    protected function getTerm()
    {
        $block     = $this->getImporter()->createTerm();
        $block_key = $this->getKey($block);
        
        if ($obj = $this->getReference($block_key)) {
            // TermBlock already exists.
            return $obj;
        }
        
        $term_key = $this->getKey($block->getTerm());
        
        if (!$term = $this->getReference($term_key)) {
            // First time the term was created. Store it.
            $this->store(($term = $block->getTerm()), $term_key);
            
            //echo "[Term - Missed]: {$term_key}\n";
        }
        
        $term->addBlock($block);
        
        return $this->store($block, $block_key);
    }
    
    /**
     * Get the subject object.
     * 
     * @return AbstractEntity|Subject
     */
    protected function getSubject()
    {
        $subject = $this->getImporter()->createSubject();
        $key     = $this->getKey($subject);
        
        if ($obj = $this->getReference($key)) {
            //echo "[Subject - Found]: {$key}\n";
            return $obj;
        }
        
        //echo "[Subject - Missing]: {$key}\n";
        
        return $this->store($subject, $key);
    }
    
    /**
     * @return AbstractEntity|Course
     */
    protected function getCourse(Subject $subject = null)
    {
        $subject = $subject ?: $this->getSubject();
        $course  = $this->getImporter()->createCourse($subject);
        $key     = $this->getKey($course);
        
        if ($obj = $this->getReference($key)) {
            /*echo "[Course - Found] {$obj->getSubject()->getName()} - {$obj->getNumber()}: "
                . count($obj->getSections()) . "\n"
            ;*/
            
            return $obj;
        }
        
        $subject->addCourse($course);
        
        //echo "[Course - Missed] {$course->getSubject()->getName()} - {$course->getNumber()}\n";
        
        return $this->store($course, $key);
    }
    
    protected function getSection(Course $course = null)
    {
        $course  = $course ?: $this->getCourse();
        $section = $this->getImporter()->createSection();
        
        $section
            ->setCourse($course)
            ->setCampus($this->getCampus())
            ->setBlock($this->getTerm())
            ->setInstructor($this->getInstructor())
            ->setRoom($this->getRoom())
            ->setSubject($this->getSubject())
        ;
        
        $key = $this->getKey($section);
        
        if ($obj = $this->getReference($key)) {
            return $obj;
        }
        
        $course->addSection($section);
        
        return $this->store($section, $key);
    }
    
    /**
     * Get a string representation of an object so that it may be fetched later.
     * 
     * @param AbstractEntity $object
     *
     * @return string
     */
    protected function getKey($object)
    {
        if ($object instanceof Campus) {
            return 'c-' . $object->getName();
        }
        
        if ($object instanceof Building) {
            return $this->getKey($object->getCampus()) . '_b-' . $object->getName();
        }
        
        if ($object instanceof Room) {
            return $this->getKey($object->getBuilding()) . '_r-' . $object->getNumber();
        }
        
        if ($object instanceof Instructor) {
            return 'i-' . $object->getId();
        }
        
        if ($object instanceof Term) {
            return $object->getYear() . '-' . $object->getSemester();
        }
        
        if ($object instanceof TermBlock) {
            return $this->getKey($object->getTerm()) . '_b-' . $object->getName();
        }
        
        if ($object instanceof Subject) {
            return 'sub-' . $object->getName();
        }
        
        if ($object instanceof Section) {
            return $this->getKey($object->getBlock()) . '_sec-' . $object->getCrn();
        }
        
        if ($object instanceof Course) {
            return $this->getKey($object->getSubject()) . " ({$object->getNumber()})";
        }
        
        throw new \LogicException('[AbstractDataFixture::getKey] Missing pattern for: ' . get_class($object));
    }
    
    /**
     * {@inheritdoc}
     */
    public function getReference($name)
    {
        try {
            $value = parent::getReference($name);
        } catch (\OutOfBoundsException $e) {
            $value = null;
        }
        
        return $value;
    }
    
    /**
     * Register the entity with the ObjectManager and add a reference to it.
     * 
     * @param AbstractEntity $entity
     * @param String         $key
     *
     * @return AbstractEntity
     */
    protected function store(AbstractEntity $entity, $key)
    {
        $this->addReference($key, $entity);
        
        $this->getDoctrine()
            ->getManager()
            ->persist($entity)
        ;
        
        return $entity;
    }
    
    protected function doWrite()
    {
        $doctrine   = $this->getDoctrine();
        $connection = $doctrine->getConnection();
        
        if (!$connection->isTransactionActive()) {
            return false;
        }
        
        $doctrine->getManager()->flush();
        
        $connection->commit();
        
        return $this;
    }
    
    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }
}