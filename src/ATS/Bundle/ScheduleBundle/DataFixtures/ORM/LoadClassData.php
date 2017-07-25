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
        $prev_term = null;
        $importer  = $this->getImporter(true);
        $progress  = static::getProgressBar(count($importer->getEntries()));
        
        $progress->setMessage('Importing section data...');
        
        while ($entry = $importer->getEntry()) {
            $subject = $this->getSubject();
            $course  = $this->getCourse($subject);
            $section = $this->getSection($course);
            $term    = $section->getBlock()->getTerm();
            
            if (!$prev_term) {
                // First cycle.
                $prev_term = $term;
            }
            
            if ($prev_term->getId() !== $term->getId()) {
                $manager->flush();
                $manager->clear();
                
                $prev_term = $term;
            }
            
            $importer->nextEntry();
            $progress->advance();
        }
        
        $manager->flush();
        $progress->finish();
        
        // Clear the line.
        static::getOutput()->writeln("\nImport complete.");
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}