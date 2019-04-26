<?php

namespace AppBundle\Service;
use AppBundle\Repository\JobRepository;
use AppBundle\Entity\Job;


class JobService
{
    private $jobRepository;

    public function __construct(JobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    // not proper format but i did what i could
    public function getFormatedJobs(?array $collumns = array(), ?int $page = 1, ?int $limit = 10)
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->jobRepository->restrictedInformationJobs(
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $jobs = $this->jobRepository->restrictedInformationJobs(
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $jobs];
    }

    public function getFormatedHarborJobs(int $harborId, ?array $collumns = array(), ?int $page = 1, ?int $limit = 10)
    {
        $limit   = ($limit > 100) ? 100 : $limit;
        $offset  = ($page - 1) * $limit;

        $count = $this->jobRepository->restrictedInformationHarborJobs(
            $harborId,
            $collumns,
            $limit,
            $offset,
            true
        );
        $count = reset($count)['count'];

        $jobs = $this->jobRepository->restrictedInformationHarborJobs(
            $harborId,
            $collumns,
            $limit,
            $offset
        );

        return ['totalItems' => $count, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($count / $limit), 'Items' => $jobs];
    }
}