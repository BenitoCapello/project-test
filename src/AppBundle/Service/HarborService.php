<?php

namespace AppBundle\Service;
use AppBundle\Repository\HarborRepository;
use AppBundle\Entity\Harbor;
use Knp\Component\Pager\PaginatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;


class HarborService
{
    private $harborRepository;
    private $paginator;
    private $serialiser;

    public function __construct(HarborRepository $harborRepository, PaginatorInterface $paginator, SerializerInterface $serialiser)
    {
        $this->harborRepository = $harborRepository;
        $this->paginator      = $paginator;
        $this->serialiser     = $serialiser;
    }

    // not proper format but i did what i could
    public function getFormatedHarbors(/*?array $collumns = array(),*/ ?int $page = 1, ?int $limit = 10): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $query = $this->harborRepository->qbAll();

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