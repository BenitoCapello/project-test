<?php

namespace AppBundle\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use AppBundle\Entity\Ship;
use AppBundle\Entity\Travel;
use AppBundle\Entity\Harbor;

/**
 * StatRepository
 *
 */
class StatRepository extends ServiceEntityRepository//\Doctrine\ORM\EntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ship::class, Travel::class, Harbor::class);
    }

    public function getHarborsShipCount(?int $limit = 10, ?int $offset = 0, ?bool $count = false): array
    {
        $travelTable = $this->getEntityManager()->getClassMetadata(Travel::class)->table["name"];
        $harborTable = $this->getEntityManager()->getClassMetadata(Harbor::class)->table["name"];

        if ($count) {
            $select = 'COUNT(final.arival_id) as count FROM (SELECT arivals.arival_id';
            $limit  = ') as final';
        }
        else {
            $select   = 'COUNT(arivals.arival_id) as ShipCount, arivals.arival_id as HarborId, h.name as HarborName';
            $limit    = ' LIMIT '. $offset .', '. $limit;
        }

        $sql =  'SELECT '. $select .'
                 FROM (
                    SELECT arival_id
                    FROM '. $travelTable .'
                    GROUP BY ship_id
                    ORDER BY travel_date DESC
                 ) arivals
                 JOIN '. $harborTable .' h ON (arivals.arival_id = h.id)
                 GROUP BY arivals.arival_id
                 ORDER BY h.id DESC'
                 .$limit;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getShipTravelCountAction(?int $shipId = null, ?string $date = null, ?string $groupType = null, ?int $limit = 10, ?int $offset = 0, ?bool $count = false): array
    {
        $travelTable = $this->getEntityManager()->getClassMetadata(Travel::class)->table["name"];
        $shipTable   = $this->getEntityManager()->getClassMetadata(Ship::class)->table["name"];
        $wheres      = [];
        $params      = [];
        $group       = '';
        $assocDate   = [0 => 'year', 1 => 'month', 2 => 'day'];

        if ($shipId) {
            $wheres[]           = 't.ship_id = :ship_id';
            $params[':ship_id'] = $shipId;
        }

        if ($date) {
            $dates           = explode('-', $date);
            foreach ($dates as $key => $date) {
                $wheres[]                     = strtoupper($assocDate[$key]).'(t.travel_date) = :'.$assocDate[$key];
                $params[':'.$assocDate[$key]] = $date;
            }
        }

        if (!empty($wheres)) {
            $where = ' WHERE '.implode(' AND ', $wheres);
        }
        else {
            $where = '';
        }

        if (!$groupType || !in_array($groupType, $assocDate)) {
            $groupType = 'day';
        }

        switch ($groupType) {
            default:
                $group .= ' YEAR(t.travel_date), MONTH(t.travel_date), DAY(t.travel_date), ';
            break;

            case 'month' :
                $group .= ' YEAR(t.travel_date), MONTH(t.travel_date), ';
            break;

            case 'year' :
                $group .= ' YEAR(t.travel_date), ';
            break;
        }

        if ($count) {
            $select = 'COUNT(final.id) as count FROM (SELECT t.id';
            $limit  = ') as final';
        }
        else {
            $select   = 'COUNT(t.id) as TravelCount, '. $group .'t.ship_id as ShipId, s.name as ShipName';
            $limit    = ' LIMIT '. $offset .', '. $limit;
        }

        $sql =  'SELECT '. $select .'
                 FROM '. $travelTable .' t
                 JOIN '. $shipTable .' s ON (t.ship_id = s.id)'
                 .$where.
                 ' GROUP BY '. $group .'t.ship_id
                   ORDER BY t.travel_date desc'
                 .$limit;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getHarborTravelCountAction(?int $harborId = null, ?string $date = null, ?string $groupType = null, ?int $limit = 10, ?int $offset = 0, ?bool $count = false): array
    {
        $travelTable = $this->getEntityManager()->getClassMetadata(Travel::class)->table["name"];
        $harborTable = $this->getEntityManager()->getClassMetadata(Harbor::class)->table["name"];
        $wheres      = [];
        $params      = [];
        $group       = '';
        $assocDate   = [0 => 'year', 1 => 'month', 2 => 'day'];

        if ($harborId) {
            $wheres[]             = 't.arival_id = :harbor_id';
            $params[':harbor_id'] = $harborId;
        }

        if ($date) {
            $dates           = explode('-', $date);
            foreach ($dates as $key => $date) {
                $wheres[]                     = strtoupper($assocDate[$key]).'(t.travel_date) = :'.$assocDate[$key];
                $params[':'.$assocDate[$key]] = $date;
            }
        }

        if (!empty($wheres)) {
            $where = ' WHERE '.implode(' AND ', $wheres);
        }
        else {
            $where = '';
        }

        if (!$groupType || !in_array($groupType, $assocDate)) {
            $groupType = 'day';
        }

        switch ($groupType) {
            default:
                $group .= ' YEAR(t.travel_date), MONTH(t.travel_date), DAY(t.travel_date), ';
            break;

            case 'month' :
                $group .= ' YEAR(t.travel_date), MONTH(t.travel_date), ';
            break;

            case 'year' :
                $group .= ' YEAR(t.travel_date), ';
            break;
        }

        if ($count) {
            $select = 'COUNT(final.id) as count FROM (SELECT t.id';
            $limit  = ') as final';
        }
        else {
            $select   = 'COUNT(t.id) as TravelCount, '. $group .'t.arival_id as HarborId, h.name as HarborName';
            $limit    = ' LIMIT '. $offset .', '. $limit;
        }

        $sql =  'SELECT '. $select .'
                 FROM '. $travelTable .' t
                 JOIN '. $harborTable .' h ON (t.arival_id = h.id)'
                 .$where.
                 ' GROUP BY '. $group .'t.arival_id
                   ORDER BY t.travel_date desc'
                 .$limit;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
