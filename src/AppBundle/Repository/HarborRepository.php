<?php

namespace AppBundle\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use AppBundle\Entity\Harbor;
use AppBundle\Entity\Ship;
use AppBundle\Entity\Travel;

/**
 * HarborRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class HarborRepository extends ServiceEntityRepository//\Doctrine\ORM\EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Harbor::class, Ship::class, Travel::class);
    }

    // This will return a QueryBuilder instance
    public function qbAll()
    {
        return $this->createQueryBuilder("h");
    }

    public function restrictedInformationHarbors(?array $collumns = ['id'], ?int $limit = 10, ?int $offset = 0, ?bool $count = false): array
    {
        $table    = $this->getClassMetadata()->table["name"];

        if ($count) {
            $select = 'COUNT(id) as count';
            $limit  = '';
        }
        else {
            $collumns = (!is_array($collumns) || empty($collumns) ? ['id'] : $collumns);
            $select   = implode(', ', $collumns);
            $limit    = ' LIMIT '. $offset .', '. $limit;
        }

        $sql =  'SELECT '. $select .' FROM '. $table . $limit;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function restrictedInformationHarbor(int $id, ?array $collumns = ['id']): ?array
    {
        $collumns = (!is_array($collumns) || empty($collumns) ? ['id'] : $collumns);
        $table    = $this->getClassMetadata()->table["name"];

        $sql =  'SELECT '. implode(', ', $collumns) .' FROM '. $table .' WHERE id = :id';

        $stmt   = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        $result = is_array($result) ? $result : null;

        return $result;
    }

    public function restrictedInformationShipHarbors(int $shipId, ?array $collumns = ['id'], ?int $limit = 10, ?int $offset = 0, ?bool $count = false): array
    {
        $shipMetas           = $this->getEntityManager()->getClassMetadata(Ship::class);
        $shipTableName       = $shipMetas->table["name"];
        $harborMetas         = $this->getEntityManager()->getClassMetadata(Harbor::class);
        $harborJobsTableName = $harborMetas->associationMappings['jobs']['joinTable']['name'];
        $table               = $this->getClassMetadata()->table["name"];

        if ($count) {
            $select = 'COUNT(harborcount.id) as count FROM (SELECT h.id';
            $limit  = ') harborcount';
        }
        else {
            $collumns = (!is_array($collumns) || empty($collumns) ? ['id'] : $collumns);
            array_walk($collumns, function(&$value) {
                $value = 'h.'.$value;
            });
            $select   = implode(', ', $collumns);
            $limit    = ' LIMIT '. $offset .', '. $limit;
        }

        $sql = 'SELECT '. $select .'
                FROM '. $table .' h 
                JOIN '. $harborJobsTableName .' hj ON (h.id = hj.harbor_id) 
                JOIN '. $shipTableName .' s ON (s.id = :ship_id AND hj.job_id = s.job_id AND s.drought <= h.drought_allowed AND s.length <= h.max_allowed_length AND s.width <= h.max_allowed_width)
                GROUP BY h.id'
                .$limit;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute([':ship_id' => $shipId]);

        return $stmt->fetchAll();
    }

    public function getFormatedDockedHarbor(int $shipId, ?array $collumns = ['id']): array
    {
        $travelMetas      = $this->getEntityManager()->getClassMetadata(Travel::class);
        $travelTableName  = $travelMetas->table["name"];
        $table            = $this->getClassMetadata()->table["name"];

        $collumns = (!is_array($collumns) || empty($collumns) ? ['id'] : $collumns);
        array_walk($collumns, function(&$value) {
            $value = 'h.'.$value;
        });
        $select   = implode(', ', $collumns);

        $sql = 'SELECT '. $select .' FROM '. $travelTableName .' t JOIN '. $table .' h ON (t.arival_id = h.id AND t.ship_id = :ship_id) ORDER BY t.travel_date DESC LIMIT 1';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute([':ship_id' => $shipId]);
        $harbor = $stmt->fetch();

        if (!$harbor) {
            $shipMetas      = $this->getEntityManager()->getClassMetadata(Ship::class);
            $shipTableName  = $shipMetas->table["name"];

            $sql = 'SELECT '. $select .' FROM '. $table .' h JOIN '. $shipTableName .' s ON (h.id = s.harbor_built_id AND s.id = :ship_id)';
            $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
            $stmt->execute([':ship_id' => $shipId]);
            $harbor = $stmt->fetch();
        }

        return $harbor;
    }
}
