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
use AppBundle\Service\CrewService;

/**
 * Crew controller.
 *
 */
class CrewController extends FOSRestController
{
    private $crewService;

    public function __construct(CrewService $crewService)
    {
        $this->crewService = $crewService;
    }

    /**
     * List of crew members.
     * @Rest\Get("/crew/list", name="crew_index")
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
     *     description="get crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Crew::class, groups={"list"}))
     *     )
     * )
     */
    public function indexAction(Request $request): JsonResponse
    {
        $formatedCrew = $this->crewService->getFormatedCrewMembers(
            ['id', 'firstname', 'lastname', 'birth_date'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedCrew);
    }

    /**
     * List of unasigned crew members.
     * @Rest\Get("/crew/unasigned", name="crew_unasigned")
     * @QueryParam(
     *   name="limit",
     *   requirements="^[1-9][0-9]?$|^100",
     *   default=10,
     *   description="item limit per page 100 maximum",
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
     *     description="get unasigned crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Crew::class, groups={"list"}))
     *     )
     * )
     */
    public function unasignedCrewAction(Request $request): JsonResponse
    {
        $formatedCrew = $this->crewService->getFormatedShipCrewMembers(
            null,
            ['id', 'firstname', 'lastname', 'birth_date'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedCrew);
    }

    /**
     * Creates a new crew entity.
     * @Rest\Post("/crew/new", name="crew_new")
     *
     * @QueryParam(
     *   name="firstname",
     *   requirements="^[a-zA-Z -]*$",
     *   description="crew firstname",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="lastname",
     *   requirements="^[a-zA-Z -]*$",
     *   description="crew lastname",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="birthdate",
     *   requirements="([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))",
     *   description="crew birthdate (yyyy-mm-dd)",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="job_id",
     *   requirements="^[1-9][0-9]*",
     *   description="crew job id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="new crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Crew::class))
     *     )
     * )
     */
    public function newAction(Request $request): JsonResponse
    {
        $em    = $this->getDoctrine()->getManager();
        $jobId = $request->query->get('job_id');
        $job   = $em->getRepository('AppBundle:Job')->find($jobId);

        if(null === $job) {
            return new JsonResponse(['message' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $crew = new Crew();
        $crew->setFirstname($request->query->get('firstname'));
        $crew->setLastname($request->query->get('lastname'));
        $crew->setBirthDate(new \DateTime($request->query->get('birthdate')));
        $crew->setJob($job);

        $em->persist($crew);
        $em->flush();

        if (!$crew->getId()) {
            return new JsonResponse(['message' => 'Something went wrong bro...'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'        => $crew->getId(),
            'firstname' => $crew->getFirstname(),
            'lastname'  => $crew->getLastname(),
            'birthdate' => $crew->getBirthDate()->format('Y-m-d H:i:s'),
            'job_id'    => $jobId
        ]);
    }

    /**
     * Finds and displays a crew entity.
     * @Rest\Post("/crew/{crewId}", name="crew_show")
     *
     * @RequestParam(
     *   name="crewId",
     *   requirements="^[1-9][0-9]*",
     *   description="crew id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="show crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Crew::class))
     *     )
     * )
     */
    public function showAction(int $crewId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $crew = $em->getRepository('AppBundle:Crew')->restrictedInformationCrewMember(
            $crewId,
            ['id', 'firstname', 'lastname', 'birth_date', 'job_id', 'ship_id']
        );

        if(null === $crew) {
            return new JsonResponse(['message' => 'Crew not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($crew);
    }

    /**
     * Resign from ship.
     * @Rest\Post("/crew/{crewId}/resign_ship", name="crew_resign")
     *
     * @RequestParam(
     *   name="crewId",
     *   requirements="^[1-9][0-9]*",
     *   description="crew id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="resign from ship"
     * )
     */
    public function resignShipAction(int $crewId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $crew = $em->getRepository('AppBundle:Crew')->find($crewId);

        if(null === $crew) {
            return new JsonResponse(['message' => 'Crew not found'], Response::HTTP_NOT_FOUND);
        }

        if(null === $crew->getShip()) {
            return new JsonResponse(['message' => 'Crew has no assigned ship'], Response::HTTP_NOT_FOUND);
        }

        $crew->setShip(null);
        $em->persist($crew);
        $em->flush();

        return new JsonResponse(['message' => 'resignation successfull']);
    }

    /**
     * Apply for ship.
     * 
     * @Rest\Post("/crew/{crewId}/apply_ship/{shipId}", name="crew_apply_ship")
     *
     * @RequestParam(
     *   name="crewId",
     *   requirements="^[1-9][0-9]*",
     *   description="crew id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="apply for ship"
     * )
     */
    public function appyShipAction(int $crewId, int $shipId): JsonResponse
    {
        $em     = $this->getDoctrine()->getManager();
        $crew   = $em->getRepository('AppBundle:Crew')->find($crewId);
        $ship   = $em->getRepository('AppBundle:Ship')->find($shipId);
        $errors = array();

        if(null === $crew) {
            $errors[] = 'Crew not found';
        }

        if(null === $ship) {
            $errors[] = 'Ship not found';
        }

        if (!empty($errors)) {
            return new JsonResponse(['message' => $errors], Response::HTTP_NOT_FOUND);
        }

        if ($crew->getShip()) {
            return new JsonResponse(['message' => 'Crew must be jobless'], Response::HTTP_NOT_FOUND);
        }

        if(false === $this->crewService->crewCanApplyShip($crew, $ship)) {
            return new JsonResponse(['message' => 'Crew job different from ship job, both entities must have the same one'], Response::HTTP_NOT_FOUND);
        }

        $crew->setShip($ship);
        $em->persist($crew);
        $em->flush();

        return new JsonResponse(['message' => 'apply successfull']);
    }

    /**
     * Edit an existing crew entity.
     *
     * @Rest\Put("/crew/edit/{crewId}", name="crew_edit")
     *
     * @RequestParam(
     *   name="crewId",
     *   requirements="^[1-9][0-9]*",
     *   description="crew id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="firstname",
     *   requirements="^[a-zA-Z -]*$",
     *   description="crew firstname",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="lastname",
     *   requirements="^[a-zA-Z -]*$",
     *   description="crew lastname",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="birthdate",
     *   requirements="([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))",
     *   description="crew birthdate (yyyy-mm-dd)",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="show crew edited",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Crew::class))
     *     )
     * )
     */
    public function editAction(Request $request, int $crewId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $crew = $em->getRepository('AppBundle:Crew')->find($crewId);

        if(null === $crew) {
            return new JsonResponse(['message' => 'Crew not found'], Response::HTTP_NOT_FOUND);
        }

        $crew->setFirstname($request->query->get('firstname'));
        $crew->setLastname($request->query->get('lastname'));
        $crew->setBirthDate(new \DateTime($request->query->get('birthdate')));

        $em->persist($crew);
        $em->flush();

        return new JsonResponse([
            'id'        => $crew->getId(),
            'firstname' => $crew->getFirstname(),
            'lastname'  => $crew->getLastname(),
            'birthdate' => $crew->getBirthDate()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Deletes a crew entity.
     *
     * @Rest\Delete("/crew/delete/{crewId}", name="crew_delete")
     *
     * @RequestParam(
     *   name="crewId",
     *   requirements="^[1-9][0-9]*",
     *   description="crew id",
     *   strict=true,
     *   nullable=false
     * )
     */
    public function deleteAction(int $crewId): JsonResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $crew = $em->getRepository('AppBundle:Crew')->find($crewId);

        if(null === $crew) {
            return new JsonResponse(['message' => 'Crew not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($crew);
        $em->flush();

        return new JsonResponse(['message' => 'deletion successfull']);
    }
}
