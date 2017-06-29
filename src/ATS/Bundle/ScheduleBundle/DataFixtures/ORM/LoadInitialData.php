<?php

namespace ATS\Bundle\ScheduleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

class LoadInitialData extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $importer = $this->getImporter(true);
        
        while ($entry = $importer->getEntry()) {
            $this->getTerm();
            $this->getRoom();
            $this->getInstructor();
            
            $importer->nextEntry();
        }
        
        $manager->flush();
    }
    
    /**
     * The lower the number, the sooner that this fixture is loaded.
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}