<?php

namespace AppBundle\Service;
use AppBundle\Repository\CrewRepository;
use AppBundle\Entity\Crew;
use AppBundle\Entity\Ship;
use Knp\Component\Pager\PaginatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;


class CrewService
{
    private $crewRepository;
    private $paginator;
    private $serialiser;

    public function __construct(CrewRepository $crewRepository, PaginatorInterface $paginator, SerializerInterface $serialiser)
    {
        $this->crewRepository = $crewRepository;
        $this->paginator      = $paginator;
        $this->serialiser     = $serialiser;
    }

    // not proper format but i did what i could
    public function getFormatedCrewMembers(/*?array $collumns = array(),*/ ?int $page = 1, ?int $limit = 10, ?bool $unasignated = false, $shipId = false): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        // $count = $this->crewRepository->restrictedInformationCrew(
        //     $collumns,
        //     $limit,
        //     $offset,
        //     true
        // );
        // $count = reset($count)['count'];

        // $crewMembers = $this->crewRepository->restrictedInformationCrew(
        //     $collumns,
        //     $limit,
        //     $offset
        // );

        $query = $this->crewRepository->qbAll();
        if ($unasignated) {
            $query->where('c.ship IS NULL');
        }

        if ($shipId) {
            $query->where('c.ship = '. $shipId);
        }

        $paginator  = $this->paginator;
        $pagination = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        $count      = $pagination->getTotalItemCount();
        $totalPages = $pagination->getPageCount();

        $data = $this->serialiser->serialize($pagination->getItems(), 'json', SerializationContext::create()->setGroups([
            'list'
        ]));

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => $totalPages, 'Items' => json_decode($data)];
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