<?php

namespace AppBundle\Service;
use AppBundle\Repository\ShipRepository;
use AppBundle\Entity\Ship;


class ShipService
{
    private $shipRepository;

    public function __construct(ShipRepository $shipRepository)
    {
        $this->shipRepository = $shipRepository;
    }

    // not proper format but i did what i could
    public function getFormatedShips(?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->shipRepository->restrictedInformationShips(
            $collumns,
            $limit,
            $offset,
            true
        );
        $count   = reset($count)['count'];

        $harbors = $this->shipRepository->restrictedInformationShips(
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $harbors];
    }

    public function getFormatedHarborShips(int $harborId, ?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->shipRepository->restrictedInformationHarborShips(
            $harborId,
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $ships = $this->shipRepository->restrictedInformationHarborShips(
            $harborId,
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $ships];
    }

    public function canAcessHarbor(int $shipId, int $harborId): bool
    {
        $harbor = $this->shipRepository->shipCanAccessHarbor(
            $shipId,
            $harborId
        );

        if ($harbor) {
            return true;
        }

        return false;
    }
}