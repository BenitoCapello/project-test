<?php

namespace AppBundle\Service;
use AppBundle\Repository\ShipRepository;
use AppBundle\Entity\Ship;
use Knp\Component\Pager\PaginatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;


class ShipService
{
    private $shipRepository;
    private $paginator;
    private $serialiser;

    public function __construct(ShipRepository $shipRepository, PaginatorInterface $paginator, SerializerInterface $serialiser)
    {
        $this->shipRepository = $shipRepository;
        $this->paginator      = $paginator;
        $this->serialiser     = $serialiser;
    }

    // not proper format but i did what i could
    public function getFormatedShips(/*?array $collumns = array(),*/ ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $query = $this->shipRepository->qbAll();

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