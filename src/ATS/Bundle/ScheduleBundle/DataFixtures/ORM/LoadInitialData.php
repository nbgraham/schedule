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
        $this->container->get('schedule.import_helper')
            ->toggleFKChecks(true)
        ;
        
        $importer = $this->getImporter(true);
        $progress = static::getProgressBar(count($importer->getEntries()));
        
        $progress->start();
        $progress->setMessage('Importing initial data...');
        
        while ($entry = $importer->getEntry()) {
            $this->getTerm();
            $this->getRoom();
            $this->getInstructor();
            
            $importer->nextEntry();
            $progress->advance();
        }
        
        $manager->flush();
        $progress->finish();
        
        // Clear the line.
        static::getOutput()->writeln('');
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