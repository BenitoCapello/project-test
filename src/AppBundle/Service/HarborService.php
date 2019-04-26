<?php

namespace AppBundle\Service;
use AppBundle\Repository\HarborRepository;
use AppBundle\Entity\Harbor;


class HarborService
{
    private $harborRepository;

    public function __construct(HarborRepository $harborRepository)
    {
        $this->harborRepository = $harborRepository;
    }

    // not proper format but i did what i could
    public function getFormatedHarbors(?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->harborRepository->restrictedInformationHarbors(
            $collumns,
            $limit,
            $offset,
            true
        );
        $count   = reset($count)['count'];

        $harbors = $this->harborRepository->restrictedInformationHarbors(
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $harbors];
    }

    public function getFormatedShipAvailableHarbors(int $shipId, ?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->harborRepository->restrictedInformationShipHarbors(
            $shipId,
            $collumns,
            $limit,
            $offset,
            true
        );
        $count   = reset($count)['count'];

        $harbors = $this->harborRepository->restrictedInformationShipHarbors(
            $shipId,
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $harbors];
    }
}