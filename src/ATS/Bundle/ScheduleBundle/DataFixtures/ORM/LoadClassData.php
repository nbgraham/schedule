<?php

namespace ATS\Bundle\ScheduleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

class LoadClassData extends AbstractDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $importer = $this->getImporter(true);
        $counter  = 0;
        
        while ($entry = $importer->getEntry()) {
            $counter++;
            
            $subject = $this->getSubject();
            $course  = $this->getCourse($subject);
            $this->getSection($course);
            
            if (0 === ($counter % 1500)) {
                $manager->flush();
            }
            
            $importer->nextEntry();
        }
        
        $manager->flush();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 5;
    }
}