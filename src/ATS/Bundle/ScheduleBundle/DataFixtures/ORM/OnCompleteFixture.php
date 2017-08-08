<?php

namespace ATS\Bundle\ScheduleBundle\DataFixtures\ORM;

use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Clean up any loose ends during the import process.
 * 
 * @author Austin Shinpaugh
 */
class OnCompleteFixture extends AbstractDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $helper = $this->container->get('schedule.import_helper');
        $log    = $helper->getLogEntry();
        
        $log
            ->setEnd(new \DateTime())
            ->setPeakMemory(memory_get_peak_usage())
            ->setStatus(UpdateLog::COMPLETED)
        ;
        
        $manager->flush();
        
        // Clear the line.
        static::getOutput()->writeln("\nImport complete.");
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}