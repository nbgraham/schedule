<?php

namespace ATS\Bundle\ScheduleBundle\Entity;

/**
 * Base entity for all app entities.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
abstract class AbstractEntity
{
    /**
     * Returns an array of identifiable information.
     * @return array
     */
    public abstract function getKey();
}