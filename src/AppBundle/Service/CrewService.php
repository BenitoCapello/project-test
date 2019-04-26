<?php

namespace AppBundle\Service;
use AppBundle\Repository\CrewRepository;
use AppBundle\Entity\Crew;
use AppBundle\Entity\Ship;


class CrewService
{
    private $crewRepository;

    public function __construct(CrewRepository $crewRepository)
    {
        $this->crewRepository = $crewRepository;
    }

    // not proper format but i did what i could
    public function getFormatedCrewMembers(?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->crewRepository->restrictedInformationCrew(
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $crewMembers = $this->crewRepository->restrictedInformationCrew(
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $crewMembers];
    }

    public function getFormatedShipCrewMembers(?int $shipId = null, ?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->crewRepository->restrictedInformationCrewForShip(
            $shipId,
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $crewMembers = $this->crewRepository->restrictedInformationCrewForShip(
            $shipId,
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $crewMembers];
    }

    public function getFormatedShipCrew(int $shipId, ?array $collumns = array(), ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->crewRepository->restrictedInformationShipCrew(
            $shipId,
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $crew = $this->crewRepository->restrictedInformationShipCrew(
            $shipId,
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $crew];
    }

    public function crewCanApplyShip(Crew $crew, Ship $ship): bool
    {
        $shipCrewMemberCount = $this->crewRepository->restrictedInformationCrewForShip(
            $ship->getId(),
            ['id'],
            10,
            0,
            true
        );
        $count = reset($shipCrewMemberCount)['count'];

        if ($ship->getCapacity() < $shipCrewMemberCount) {
            if ($ship->getJob() === $crew->getJob()) {
                return true;
            }
        }

        return false;
    }
}