<?php

namespace AppBundle\Service;
use AppBundle\Repository\JobRepository;
use AppBundle\Entity\Job;
use Knp\Component\Pager\PaginatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;


class JobService
{
    private $jobRepository;
    private $paginator;
    private $serialiser;

    public function __construct(JobRepository $jobRepository, PaginatorInterface $paginator, SerializerInterface $serialiser)
    {
        $this->jobRepository = $jobRepository;
        $this->paginator     = $paginator;
        $this->serialiser    = $serialiser;
    }

    // not proper format but i did what i could
    public function getFormatedJobs(/*?array $collumns = array(),*/ ?int $page = 1, ?int $limit = 10, $harborId = null): array
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $query = $this->jobRepository->qbAll();
        if ($harborId) {
            $query->innerJoin('j.harbors', 'h', 'WITH', 'h.id = '.$harborId);
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
}