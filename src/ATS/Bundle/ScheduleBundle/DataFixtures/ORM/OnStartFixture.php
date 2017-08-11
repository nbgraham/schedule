<?php

namespace ATS\Bundle\ScheduleBundle\DataFixtures\ORM;

use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
use ATS\Bundle\ScheduleBundle\Util\Parser\OdsImportDriver;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * First fixture loaded - do some init the UpdateLog.
 * 
 * @author Austin Shinpaugh
 */
class OnStartFixture extends AbstractDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $helper = $this->container->get('schedule.import_helper');
        $import = $this->container->get($this->service_id);
        $source = 'book';
        
        if ($import instanceof OdsImportDriver) {
            $source = 'ods';
        }
        
        foreach ($helper->getUpdateLogs() as $log) {
            $manager->persist($log);
        }
        
        $manager->persist(new UpdateLog($source));
        $manager->flush();
        
        // Free up memory.
        $helper->clearLogs();
        
        /*
         * If the command was run manually, the HttpCache won't invalidate
         * naturally and needs to be purged.
         */
        $this->clearEdgeSideInclude();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}