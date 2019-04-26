<?php

namespace AppBundle\Service;
use AppBundle\Repository\TravelRepository;
use AppBundle\Entity\Travel;


class TravelService
{
    private $travelRepository;

    public function __construct(TravelRepository $travelRepository)
    {
        $this->travelRepository = $travelRepository;
    }

    // not proper format but i did what i could
    public function getFormatedTravels(int $shipId, ?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->travelRepository->restrictedInformationTravels(
            $shipId,
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $travels = $this->travelRepository->restrictedInformationTravels(
            $shipId,
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $travels];
    }
}