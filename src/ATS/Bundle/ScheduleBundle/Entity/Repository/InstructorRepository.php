<?php

namespace ATS\Bundle\ScheduleBundle\Entity\Repository;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityRepository;

/**
 * Instructor repository.
 * 
 * @author Austin Shinpaugh
 */
class InstructorRepository extends EntityRepository
{
    /**
     * Group the instructors by the subjects they teach.
     * 
     * Teachers who teach courses that belong to different subjects are duplicated
     * under each new subject.
     * 
     * @return array
     */
    public function getInstructorsBySubject()
    {
        /* @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        
        $statement = $conn->prepare('
            SELECT sub.id, sub.name, i.id, any_value(i.email), any_value(i.name) as i_name
            FROM section AS s
            JOIN subject AS sub
              ON s.subject_id = sub.id
            JOIN instructor AS i
              ON s.instructor_id = i.id
            WHERE s.instructor_id != 0
            GROUP BY sub.id, i.id
            ORDER BY i_name;
        ');
        
        $statement->execute();
        $results = [];
        
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $id   = $item['subject_id'];
            $name = $item['subject_name'];
            
            unset($item['subject_id']);
            unset($item['subject_name']);
            
            if (!array_key_exists($id, $results)) {
                $results[$id] = [
                    'name'        => $name,
                    'instructors' => []
                ];
            }
            
            $results[$id]['instructors'][] = $item;
        }
        
        return $results;
    }
}