<?php

namespace ATS\Bundle\ScheduleBundle\Util\Helper;

use ATS\Bundle\ScheduleBundle\Entity\UpdateLog;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Helper for the import driver system. Captures input from the command to be
 * used in the fixtures.
 * 
 * @author Austin Shinpaugh
 */
class ImportDriverHelper
{
    /**
     * @var Registry
     */
    protected $doctrine;
    
    /**
     * @var UpdateLog[]
     */
    protected $logs;
    
    /**
     * @var String
     */
    protected $service_id;
    
    /**
     * @var String
     */
    protected $academic_period;
    
    /**
     * @var Integer
     */
    protected $num_years;
    
    /**
     * ImportDriverHelper constructor.
     *
     * @param Registry $doctrine
     * @param Integer  $num_years
     */
    public function __construct(Registry $doctrine, $num_years)
    {
        $this->doctrine  = $doctrine;
        $this->num_years = $num_years;
        $this->logs      = [];
        
        $this->fetchUpdateLogs();
    }
    
    /**
     * Get the service id of the driver being used.
     * 
     * @return String
     */
    public function getServiceId()
    {
        return $this->service_id;
    }
    
    /**
     * Sets the service id.
     * 
     * @param string $id
     *
     * @return $this
     * @throws \ErrorException
     */
    public function setServiceId($id)
    {
        if (!static::isValidImportId($id)) {
            throw new \ErrorException("Invalid input provided for source option. Must be either 'book' or 'ods'.");
        }
        
        $this->service_id = sprintf("schedule.%s_import", $id);
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getAcademicPeriod()
    {
        return $this->academic_period;
    }
    
    /**
     * @param string $period The year to start from.
     *
     * @return $this
     */
    public function setAcademicPeriod($period)
    {
        if (!$period) {
            $this->academic_period = null;
            
            return $this;
        }
        
        if ('all' === $period) {
            return $this->setAcademicPeriod(
                date('Y') - $this->num_years
            );
        }
        
        $this->academic_period = ($period - 1) . '20';
        
        return $this;
    }
    
    /**
     * Accepts two points to byref assign values based on the
     * input taken from the command line.
     * 
     * @param Integer $start
     * @param Integer $stop
     *
     * @return $this
     */
    public function assignAcademicPoints(&$start, &$stop)
    {
        if ($this->academic_period) {
            $start = $this->academic_period;
        } else {
            $start = (int) ((date('Y') - $this->num_years) . '00');
        }
        
        $stop = 300000;
        
        return $this;
    }
    
    /**
     * Validate the service ID.
     * 
     * @param string $id
     *
     * @return bool
     */
    public static function isValidImportId($id)
    {
        return in_array($id, ['book', 'ods']);
    }
    
    /**
     * FK Checks need to be disabled when using TRUNCATE instead of DELETE
     * during the :fixtures:load command.
     * 
     * @param boolean $enabled
     *
     * @return int
     */
    public function toggleFKChecks($enabled)
    {
        $connection = $this->doctrine->getConnection();
        
        return $connection->executeUpdate(sprintf(
            "SET foreign_key_checks = %b;",
            (int) $enabled
        ));
    }
    
    /**
     * Get the previous logs. The import command wipes the databases, so
     * fetch them before they are destroyed.
     * 
     * Try to keep a month's worth of logs.
     * 
     * @return $this
     */
    protected function fetchUpdateLogs()
    {
        $manager = $this->doctrine->getManager();
        $repo    = $manager->getRepository(UpdateLog::class);
        $logs    = $repo->findBy([], ['start' => 'DESC'], 31);
        
        // For re-storing purposes, store from oldest to newest.
        foreach (array_reverse($logs) as $log) {
            $manager->detach($log);
            
            $this->logs[] = $log;
        }
        
        return $this;
    }
    
    /**
     * @return UpdateLog[]
     */
    public function getUpdateLogs()
    {
        return $this->logs;
    }
    
    /**
     * Remove the logs after they've been stored.
     * 
     * @return $this
     */
    public function clearLogs()
    {
        unset($this->logs);
        
        $this->doctrine->getManager()->clear();
        
        return $this;
    }
    
    /**
     * Fetch the current UpdateLog.
     * 
     * @return UpdateLog
     */
    public function getLogEntry()
    {
        $repo = $this->doctrine->getRepository(UpdateLog::class);
        
        return current($repo->findBy([], ['start' => 'DESC'], 1));
    }
}