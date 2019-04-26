<?php

namespace AppBundle\Service;
use AppBundle\Repository\StatRepository;


class StatService
{
    private $statRepository;

    public function __construct(StatRepository $statRepository)
    {
        $this->statRepository = $statRepository;
    }

    public function getHarborsShipCount(?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->statRepository->getHarborsShipCount(
            $limit,
            $offset,
            true
        );
        $count   = reset($count)['count'];

        $result = $this->statRepository->getHarborsShipCount(
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $result];
    }

    public function getShipTravelCountPerDate(?int $shipId = null, ?string $date = null, ?string $groupType = null, ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->statRepository->shipTravelCountAction(
            $shipId,
            $date,
            $groupType,
            $limit,
            $offset,
            true
        );
        $count   = reset($count)['count'];

        $result = $this->statRepository->shipTravelCountAction(
            $shipId,
            $date,
            $groupType,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $result];
    }

    public function getHarborTravelCountPerDate(?int $harborId = null, ?string $date = null, ?string $groupType = null, ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->statRepository->getHarborTravelCountAction(
            $harborId,
            $date,
            $groupType,
            $limit,
            $offset,
            true
        );
        $count   = reset($count)['count'];

        $result = $this->statRepository->getHarborTravelCountAction(
            $harborId,
            $date,
            $groupType,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $result];
    }
}