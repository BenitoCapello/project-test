<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Harbor;
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
use AppBundle\Service\HarborService;
use AppBundle\Service\JobService;
use AppBundle\Service\ShipService;
use JMS\Serializer\SerializationContext;

/**
 * Harbor controller.
 *
 */
class HarborController extends FOSRestController
{
    private $harborService;
    private $jobService;
    private $shipService;

    public function __construct(HarborService $harborService, JobService $jobService, ShipService $shipService)
    {
        $this->harborService = $harborService;
        $this->jobService    = $jobService;
        $this->shipService   = $shipService;
    }

    /**
     * List of harbors.
     * @Rest\Get("/harbor/list", name="harbor_index")
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
     *     description="get harbors",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Harbor::class, groups={"list"}))
     *     )
     * )
     */
    public function indexAction(Request $request): JsonResponse
    {
        $data = $this->harborService->getFormatedHarbors(
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($data);
    }

    /**
     * List of allowed jobs for harbor.
     * @Rest\Post("/harbor/{harborId}/job/list", name="harbor_job_index")
     *
     * @RequestParam(
     *   name="harborId",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor id",
     *   strict=true,
     *   nullable=false
     * )
     * 
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
     *     description="get harbor jobs",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Job::class, groups={"list"}))
     *     )
     * )
     */
    public function harborJobsAction(Request $request, int $harborId): JsonResponse
    {
        $data = $this->jobService->getFormatedJobs(
            $request->query->get('page'),
            $request->query->get('limit'),
            $harborId
        );

        return new JsonResponse($data);
    }

    /**
     * List of ships currently docked in harbor
     * @Rest\Post("/harbor/{harborId}/ship/list", name="harbor_ships_index")
     *
     * @RequestParam(
     *   name="harborId",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor id",
     *   strict=true,
     *   nullable=false
     * )
     * 
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
     *     description="get ships docked in harbor",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Job::class, groups={"list"}))
     *     )
     * )
     */
    public function harborShipsAction(Request $request, int $harborId): JsonResponse
    {
        $formatedShips = $this->shipService->getFormatedHarborShips(
            $harborId,
            ['id', 'name', 'unique_id', 'drought', 'length', 'width', 'capacity', 'power_type', 'engine_power', 'sail_max_heigh', 'sail_count', 'date_creation'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedShips);
    }

    /**
     * Creates a new harbor entity (jobs are not persisted, dont know why).
     * @Rest\Post("/harbor/new", name="harbor_new")
     *
     * @QueryParam(
     *   name="name",
     *   requirements="^[a-zA-Z -]*$",
     *   description="harbor name",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="drought_allowed",
     *   requirements="^\d*\.?\d?$",
     *   description="harbor maximum drought allowed for ship entering",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="max_allowed_length",
     *   requirements="^\d*\.?\d?$",
     *   description="harbor maximum legnth allowed for ship entering",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="max_allowed_width",
     *   requirements="^\d*\.?\d?$",
     *   description="harbor maximum width allowed for ship entering",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="accommodation_capacity",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor maximum number of ship allowed in the same time at dock (not handled)",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="job_ids",
     *   description="one or multiple job ids for the harbor to accept (delimiter ',')",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="new crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Harbor::class))
     *     )
     * )
     */
    public function newAction(Request $request)
    {
        $em       = $this->getDoctrine()->getManager();
        $jobIds   = explode(',', $request->query->get('job_ids'));
        $errors   = array();
        $jobs     = array();

        foreach($jobIds as $jobId) {
            if ($job = $em->getRepository('AppBundle:Job')->find($jobId)) {
                $jobs[] = $job;
            }
            else {
                $errors[] = $jobId;
            }
        }

        if(!empty($errors)) {
            return new JsonResponse(['message' => 'Job(s) '. implode(', ', $errors) .' not found'], Response::HTTP_NOT_FOUND);
        }

        $harbor = new Harbor();
        $harbor->setName($request->query->get('name'));
        $harbor->setDroughtAllowed($request->query->get('drought_allowed'));
        $harbor->setMaxAllowedLength($request->query->get('max_allowed_length'));
        $harbor->setMaxAllowedWidth($request->query->get('max_allowed_width'));
        $harbor->setAccommodationCapacity($request->query->get('accommodation_capacity'));

        $em->persist($harbor);
        $em->flush();

        if (!$harbor->getId()) {
            return new JsonResponse(['message' => 'Something went wrong bro...'], Response::HTTP_NOT_FOUND);
        }

        //not working yet
        $harbor->addJobs($jobs);
        //$em->persist($harbor);
        $em->flush();

        $data = $this->get('jms_serializer')->serialize($harbor, 'json', SerializationContext::create()->setGroups([
            'detail',
            'jobs'  => ['list']
        ]));

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Finds and displays an harbor entity.
     * @Rest\Post("/harbor/{harborId}", name="harbor_show")
     *
     * @RequestParam(
     *   name="harborId",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="show harbor",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Harbor::class))
     *     )
     * )
     */
    public function showAction(int $harborId)
    {
        $em     = $this->getDoctrine()->getManager();
        $harbor = $em->getRepository('AppBundle:Harbor')->find($harborId);

        if(null === $harbor) {
            return new JsonResponse(['message' => 'Harbor not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('jms_serializer')->serialize($harbor, 'json', SerializationContext::create()->setGroups([
            'detail',
            'jobs'  => ['list']
        ]));

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Edit an existing harbor entity.
     *
     * @Rest\Put("/harbor/edit/{harborId}", name="harbor_edit")
     *
     * @RequestParam(
     *   name="harborId",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="name",
     *   requirements="^[a-zA-Z -]*$",
     *   description="harbor name",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="drought_allowed",
     *   requirements="^\d*\.?\d?$",
     *   description="harbor maximum drought allowed for ship entering",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="max_allowed_length",
     *   requirements="^\d*\.?\d?$",
     *   description="harbor maximum legnth allowed for ship entering",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="max_allowed_width",
     *   requirements="^\d*\.?\d?$",
     *   description="harbor maximum width allowed for ship entering",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="accommodation_capacity",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor maximum number of ship allowed in the same time at dock (not handled)",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="show harbor edited",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Harbor::class))
     *     )
     * )
     */
    public function editAction(Request $request, int $harborId): JsonResponse
    {
        $em     = $this->getDoctrine()->getManager();
        $harbor = $em->getRepository('AppBundle:Harbor')->find($harborId);

        if(null === $harbor) {
            return new JsonResponse(['message' => 'Harbor not found'], Response::HTTP_NOT_FOUND);
        }

        $harbor->setName($request->query->get('name'));
        $harbor->setDroughtAllowed($request->query->get('drought_allowed'));
        $harbor->setMaxAllowedLength($request->query->get('max_allowed_length'));
        $harbor->setMaxAllowedWidth($request->query->get('max_allowed_width'));
        $harbor->setAccommodationCapacity($request->query->get('accommodation_capacity'));

        $em->persist($harbor);
        $em->flush();

        $data = $this->get('jms_serializer')->serialize($harbor, 'json', SerializationContext::create()->setGroups([
            'detail',
            'jobs'  => ['list']
        ]));

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Deletes an harbor entity (you are not supposed to delete harbors unless you removed relations before).
     *
     * @Rest\Delete("/harbor/delete/{harborId}", name="harbor_delete")
     *
     * @RequestParam(
     *   name="harborId",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor id",
     *   strict=true,
     *   nullable=false
     * )
     */
    public function deleteAction(int $harborId): JsonResponse
    {
        $em     = $this->getDoctrine()->getManager();
        $harbor = $em->getRepository('AppBundle:Harbor')->find($harborId);

        if(null === $harbor) {
            return new JsonResponse(['message' => 'Harbor not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($harbor);
        $em->flush();

        return new JsonResponse(['message' => 'deletion successfull']);
    }
}
