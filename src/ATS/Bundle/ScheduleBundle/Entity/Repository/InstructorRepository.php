<?php

namespace ATS\Bundle\ScheduleBundle\Entity\Repository;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityRepository;

class InstructorRepository extends EntityRepository
{
    public function getInstructorsBySubject()
    {
        /* @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        
        $statement = $conn->prepare('
            SELECT sub.id AS subject_id, sub.name AS subject_name,
              i.id, i.email, i.name
            FROM section AS s
            JOIN subject AS sub
              ON s.subject_id = sub.id
            JOIN instructor AS i
              ON s.instructor_id = i.id
            GROUP BY sub.id, i.id
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