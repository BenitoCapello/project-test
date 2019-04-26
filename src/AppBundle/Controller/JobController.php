<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Crew;
use AppBundle\Entity\Ship;
use AppBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Swagger\Annotations as SWG;
use AppBundle\Service\JobService;

/**
 * Job controller.
 *
 */
class JobController extends FOSRestController
{
    private $jobService;

    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    /**
     * List of jobs.
     * @Rest\Get("/job/list", name="job_index")
     * @QueryParam(
     *   name="limit",
     *   default=10,
     *   description="item limit per page 100 maximum",
     *   requirements="^[1-9][0-9]?$|^100",
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="page",
     *   default=1,
     *   description="page number",
     *   nullable=false
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="get jobs",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Job::class, groups={"list"}))
     *     )
     * )
     */
    public function indexAction(Request $request): JsonResponse
    {
        $formatedJob = $this->jobService->getFormatedJobs(
            ['id', 'name'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedJob);
    }

    /**
     * Creates a new job entity.
     * @Rest\Post("/job/new", name="job_new")
     *
     * @QueryParam(
     *   name="name",
     *   requirements="^[a-zA-Z -]*$",
     *   description="job name",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="new job",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Job::class))
     *     )
     * )
     */
    public function newAction(Request $request): JsonResponse
    {
        $em    = $this->getDoctrine()->getManager();

        $job = new Job();
        $job->setName($request->query->get('name'));

        $em->persist($job);
        $em->flush();

        if (!$job->getId()) {
            return new JsonResponse(['message' => 'Something went wrong bro...'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'    => $job->getId(),
            'name'  => $job->getName()
        ]);
    }

    /**
     * Finds and displays a job entity.
     * @Rest\Post("/job/{jobId}", name="job_show")
     *
     * @RequestParam(
     *   name="jobId",
     *   requirements="^[1-9][0-9]*",
     *   description="job id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="show job",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Job::class))
     *     )
     * )
     */
    public function showAction(int $jobId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $job = $em->getRepository('AppBundle:Job')->restrictedInformationJob(
            $jobId,
            ['id', 'name']
        );

        if(null === $job) {
            return new JsonResponse(['message' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($job);
    }

    /**
     * Edit an existing job entity.
     *
     * @Rest\Put("/job/edit/{jobId}", name="job_edit")
     *
     * @RequestParam(
     *   name="jobId",
     *   requirements="^[1-9][0-9]*",
     *   description="job id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="name",
     *   requirements="^[a-zA-Z -]*$",
     *   description="job name",
     *   strict=true,
     *   nullable=false
     * )
     *
     * * @SWG\Response(
     *     response=200,
     *     description="show job edited",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Job::class))
     *     )
     * )
     */
    public function editAction(Request $request, int $jobId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $job  = $em->getRepository('AppBundle:Job')->find($jobId);

        if(null === $job) {
            return new JsonResponse(['message' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $job->setName($request->query->get('name'));

        $em->persist($job);
        $em->flush();

        return new JsonResponse([
            'id'        => $job->getId(),
            'name'      => $job->getName()
        ]);
    }

    /**
     * Deletes a job entity (you are not supposed to delete jobs unless you removed relations before).
     *
     * @Rest\Delete("/job/delete/{jobId}", name="job_delete")
     *
     * @RequestParam(
     *   name="jobId",
     *   requirements="^[1-9][0-9]*",
     *   description="job id",
     *   strict=true,
     *   nullable=false
     * )
     */
    public function deleteAction(int $jobId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $job  = $em->getRepository('AppBundle:Job')->find($jobId);

        if(null === $job) {
            return new JsonResponse(['message' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($job);
        $em->flush();

        return new JsonResponse(['message' => 'deletion successfull']);
    }
}
