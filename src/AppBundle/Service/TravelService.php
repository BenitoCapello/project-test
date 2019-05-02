<?php

namespace AppBundle\Service;
use AppBundle\Repository\TravelRepository;
use AppBundle\Entity\Travel;
use Knp\Component\Pager\PaginatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class TravelService
{
    private $travelRepository;
    private $paginator;
    private $serialiser;

    public function __construct(TravelRepository $travelRepository, PaginatorInterface $paginator, SerializerInterface $serialiser)
    {
        $this->travelRepository = $travelRepository;
        $this->paginator        = $paginator;
        $this->serialiser       = $serialiser;
    }

    // not proper format but i did what i could
    public function getFormatedTravels(int $shipId, /*?array $collumns = array(),*/ ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $query = $this->travelRepository->qbAll();
        $query->where('t.ship = '. $shipId);

        $paginator  = $this->paginator;
        $pagination = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        $count      = $pagination->getTotalItemCount();
        $totalPages = $pagination->getPageCount();

        $data = $this->serialiser->serialize($pagination->getItems(), 'json', SerializationContext::create()->setGroups([
            'list',
            'ship'            => ['list'],
            'harborDeparture' => ['list'],
            'harborArival'    => ['list'],
        ]));

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => $totalPages, 'Items' => json_decode($data)];
    }
}