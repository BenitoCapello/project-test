<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Harbor;
use AppBundle\Entity\Ship;
use AppBundle\Entity\Job;
use AppBundle\Entity\Crew;
use AppBundle\Entity\Travel;
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
use AppBundle\Service\ShipService;
use AppBundle\Service\CrewService;
use AppBundle\Service\HarborService;
use AppBundle\Service\TravelService;
use Ramsey\Uuid\Uuid;

/**
 * Ship controller.
 *
 */
class ShipController extends FOSRestController
{
    private $shipService;
    private $crewService;
    private $harborService;
    private $travelService;

    public function __construct(ShipService $shipService, CrewService $crewService, HarborService $harborService, TravelService $travelService)
    {
        $this->shipService   = $shipService;
        $this->crewService   = $crewService;
        $this->harborService = $harborService;
        $this->travelService = $travelService;
    }

    /**
     * List of ships.
     * @Rest\Get("/ship/list", name="ship_index")
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
     *     description="get ships",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ship::class, groups={"list"}))
     *     )
     * )
     */
    public function indexAction(Request $request): JsonResponse
    {
        $formatedShips = $this->shipService->getFormatedShips(
            ['id', 'name', 'unique_id', 'drought', 'length', 'width', 'capacity', 'power_type', 'engine_power', 'sail_max_heigh', 'sail_count', 'date_creation'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedShips);
    }

    /**
     * List of crew for ship.
     * @Rest\Post("/ship/{shipId}/crew/list", name="ship_crew_index")
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
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
     *     description="get ship crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Crew::class, groups={"list"}))
     *     )
     * )
     */
    public function shipCrewAction(Request $request, int $shipId): JsonResponse
    {
        $formatedCrew = $this->crewService->getFormatedShipCrew(
            $shipId,
            ['id', 'firstname', 'lastname', 'birth_date'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedCrew);
    }

    /**
     * Travel histroy for ship.
     * @Rest\Post("/ship/{shipId}/travel/list", name="ship_travel_index")
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
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
     *     description="get ship travel history",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Travel::class, groups={"list"}))
     *     )
     * )
     */
    public function shipTravelsAction(Request $request, int $shipId): JsonResponse
    {
        $formatedTravel = $this->travelService->getFormatedTravels(
            $shipId,
            ['id', 'ship_id', 'departure_id', 'travel_date', 'arival_id'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedTravel);
    }

    /**
     * List of available harbors for ship to sail (harbor must accept ship job, and have limits above ship drought lenght and width).
     * @Rest\Post("/ship/{shipId}/harbors/available", name="ship_habors_index")
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
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
     *     description="get available harbors",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Harbor::class, groups={"list"}))
     *     )
     * )
     */
    public function shipAvailableHarborsAction(Request $request, int $shipId): JsonResponse
    {
        $formatedCrew = $this->harborService->getFormatedShipAvailableHarbors(
            $shipId,
            ['id', 'name', 'drought_allowed', 'max_allowed_length', 'max_allowed_width', 'accommodation_capacity'],
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedCrew);
    }

    /**
     * Return the habor the ship is currently docked at.
     * @Rest\Post("/ship/{shipId}/harbor/docked", name="ship_habor_index")
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
     *     description="get docked harbor",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Harbor::class))
     *     )
     * )
     */
    public function shipDockedHarborAction(Request $request, int $shipId): JsonResponse
    {
        $em             = $this->getDoctrine()->getManager();
        $formatedHarbor = $em->getRepository('AppBundle:Harbor')->getFormatedDockedHarbor(
            $shipId,
            ['id', 'name', 'drought_allowed', 'max_allowed_length', 'max_allowed_width', 'accommodation_capacity']
        );

        return new JsonResponse($formatedHarbor);
    }

    /**
     * Creates a travel for a ship.
     * @Rest\Post("/ship/{shipId}/travel/to/{harborId}", name="ship_travel_new")
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @RequestParam(
     *   name="harborId",
     *   requirements="^[1-9][0-9]*",
     *   description="harbor you want to travel to (must accept job and ship properties)",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="new travel for ship",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Travel::class))
     *     )
     * )
     */
    public function newTravelAction(Request $request, int $shipId, int $harborId): JsonResponse
    {
        $em       = $this->getDoctrine()->getManager();
        $errors   = array();
        $jobs     = array();

        $ship     = $em->getRepository('AppBundle:Ship')->find($shipId);

        if(!$ship) {
            return new JsonResponse(['message' => 'Ship not found'], Response::HTTP_NOT_FOUND);
        }

        $harbor   = $em->getRepository('AppBundle:Harbor')->find($harborId);

        if(!$harbor) {
            return new JsonResponse(['message' => 'Harbor not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->shipService->canAcessHarbor($shipId, $harborId)) {
            return new JsonResponse(['message' => 'Ship cannot access harbor'], Response::HTTP_NOT_FOUND);
        }

        $departureHarbor = $em->getRepository('AppBundle:Harbor')->getFormatedDockedHarbor($shipId);
        $departureHarbor = $em->getRepository('AppBundle:Harbor')->find($departureHarbor['id']);

        $travel = new Travel();
        $travel->setShip($ship);
        $travel->setTravelDate(new \DateTime());
        $travel->setHarborDeparture($departureHarbor);
        $travel->setHarborArival($harbor);

        $em->persist($travel);
        $em->flush();

        if (!$travel->getId()) {
            return new JsonResponse(['message' => 'Something went wrong bro...'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'             => $travel->getId(),
            'ship_id'        => $shipId,
            'departure_id'   => $departureHarbor->getId(),
            'arival_id'      => $harborId,
            'date_creation'  => $travel->getTravelDate()->format('Y-m-d')
        ]);
    }

    /**
     * Creates a new ship entity.
     * @Rest\Post("/ship/new", name="ship_new")
     *
     * @QueryParam(
     *   name="name",
     *   requirements="^[a-zA-Z -]*$",
     *   description="ship name",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="drought",
     *   requirements="^\d*\.?\d?$",
     *   description="ship drought",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="length",
     *   requirements="^\d*\.?\d?$",
     *   description="ship legnth",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="width",
     *   requirements="^\d*\.?\d?$",
     *   description="ship width",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="capacity",
     *   requirements="^[1-9][0-9]*",
     *   description="ship capacity",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="power_type",
     *   requirements="^[0-2]$",
     *   description="power type (0 : sail, 1 : engine, 2 : both)",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="engine_power",
     *   requirements="^[0-9][0-9]*",
     *   description="engine type (if power type is 1 or 2)",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="sail_max_heigh",
     *   requirements="^\d*\.?\d?$",
     *   description="ship s highest sail heigh (if power type is 0 or 2)",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="sail_count",
     *   requirements="^[0-9]$",
     *   description="ship s sail count (if power type is 0 or 2)",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="date_creation",
     *   requirements="([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))",
     *   description="ship date creation (yyyy-mm-dd)",
     *   strict=true,
     *   nullable=false
     * )
     *
     *@QueryParam(
     *   name="habor_built_id",
     *   requirements="^[1-9][0-9]*",
     *   description="id harbor where ship was contructed",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="job_id",
     *   requirements="^[1-9][0-9]*",
     *   description="ship assigned job",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="new crew",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ship::class))
     *     )
     * )
     */
    public function newAction(Request $request): JsonResponse
    {
        $em       = $this->getDoctrine()->getManager();
        $job      = $em->getRepository('AppBundle:Job')->find($request->query->get('job_id'));

        if(!$job) {
            return new JsonResponse(['message' => 'Job not found'], Response::HTTP_NOT_FOUND);
        }

        $harbor   = $em->getRepository('AppBundle:Harbor')->find($request->query->get('habor_built_id'));

        if(!$harbor) {
            return new JsonResponse(['message' => 'Harbor not found'], Response::HTTP_NOT_FOUND);
        }

        $enginePower  = ('' === $request->query->get('engine_power')) ? null : $request->query->get('engine_power');
        $sailMaxHeigh = ('' === $request->query->get('sail_max_heigh')) ? null : $request->query->get('sail_max_heigh');
        $sailCount    = ('' === $request->query->get('sail_count')) ? null : $request->query->get('sail_count');


        $ship = new Ship();
        $ship->setName($request->query->get('name'));
        $ship->setUniqueId(Uuid::uuid4()->toString());
        $ship->setDrought($request->query->get('drought'));
        $ship->setLength($request->query->get('length'));
        $ship->setWidth($request->query->get('width'));
        $ship->setCapacity($request->query->get('capacity'));
        $ship->setPowerType($request->query->get('power_type'));
        $ship->setEnginePower($enginePower);
        $ship->setSailMaxHeigh($sailMaxHeigh);
        $ship->setSailCount($sailCount);
        $ship->setDateCreation(new \DateTime($request->query->get('date_creation')));
        $ship->setHarborBuilt($harbor);
        $ship->setJob($job);

        $em->persist($ship);
        $em->flush();

        if (!$ship->getId()) {
            return new JsonResponse(['message' => 'Something went wrong bro...'], Response::HTTP_NOT_FOUND);
        }

        $em->flush();

        return new JsonResponse([
            'id'             => $ship->getId(),
            'name'           => $ship->getName(),
            'unique_id'      => $ship->getUniqueId(),
            'drought'        => $ship->getDrought(),
            'length'         => $ship->getLength(),
            'width'          => $ship->getWidth(),
            'capacity'       => $ship->getCapacity(),
            'power_type'     => $ship->getPowerType(),
            'engine_power'   => $ship->getEnginePower(),
            'sail_max_heigh' => $ship->getSailMaxHeigh(),
            'sail_count'     => $ship->getSailCount(),
            'date_creation'  => $ship->getDateCreation()->format('Y-m-d')
        ]);
    }

    /**
     * Finds and displays a ship entity.
     * @Rest\Post("/ship/{shipId}", name="ship_show")
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
     *     description="show ship",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ship::class))
     *     )
     * )
     */
    public function showAction(int $shipId): JsonResponse
    {
        $em     = $this->getDoctrine()->getManager();
        $ship   = $em->getRepository('AppBundle:Ship')->restrictedInformationShip(
            $shipId,
            ['id', 'name', 'unique_id', 'drought', 'length', 'width', 'capacity', 'power_type', 'engine_power', 'sail_max_heigh', 'sail_count', 'date_creation']
        );

        if(null === $ship) {
            return new JsonResponse(['message' => 'Ship not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($ship);
    }

    /**
     * Edit an existing ship entity.
     *
     * @Rest\Put("/ship/edit/{shipId}", name="ship_edit")
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="name",
     *   requirements="^[a-zA-Z -]*$",
     *   description="ship name",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="drought",
     *   requirements="^\d*\.?\d?$",
     *   description="ship drought",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="length",
     *   requirements="^\d*\.?\d?$",
     *   description="ship legnth",
     *   strict=true,
     *   nullable=false
     * )
     * 
     * @QueryParam(
     *   name="width",
     *   requirements="^\d*\.?\d?$",
     *   description="ship width",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="capacity",
     *   requirements="^[1-9][0-9]*",
     *   description="ship capacity",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="power_type",
     *   requirements="^[0-2]$",
     *   description="power type (0 : sail, 1 : engine, 2 : both)",
     *   strict=true,
     *   nullable=false
     * )
     *
     * @QueryParam(
     *   name="engine_power",
     *   requirements="^[0-9][0-9]*",
     *   description="engine type (if power type is 1 or 2)",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="sail_max_heigh",
     *   requirements="^\d*\.?\d?$",
     *   description="ship s highest sail heigh (if power type is 0 or 2)",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="sail_count",
     *   requirements="^[0-9]$",
     *   description="ship s sail count (if power type is 0 or 2)",
     *   strict=true,
     *   nullable=true
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="show harbor edited",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Ship::class))
     *     )
     * )
     */
    public function editAction(Request $request, int $shipId): JsonResponse
    {
        $em     = $this->getDoctrine()->getManager();
        $ship   = $em->getRepository('AppBundle:Ship')->find($shipId);

        if(null === $ship) {
            return new JsonResponse(['message' => 'Ship not found'], Response::HTTP_NOT_FOUND);
        }

        $enginePower  = ('' === $request->query->get('engine_power')) ? null : $request->query->get('engine_power');
        $sailMaxHeigh = ('' === $request->query->get('sail_max_heigh')) ? null : $request->query->get('sail_max_heigh');
        $sailCount    = ('' === $request->query->get('sail_count')) ? null : $request->query->get('sail_count');

        $ship->setName($request->query->get('name'));
        $ship->setDrought($request->query->get('drought'));
        $ship->setLength($request->query->get('length'));
        $ship->setWidth($request->query->get('width'));
        $ship->setCapacity($request->query->get('capacity'));
        $ship->setPowerType($request->query->get('power_type'));
        $ship->setEnginePower($enginePower);
        $ship->setSailMaxHeigh($sailMaxHeigh);
        $ship->setSailCount($sailCount);

        //$em->persist($ship);
        $em->flush();

        return new JsonResponse([
            'id'             => $ship->getId(),
            'name'           => $ship->getName(),
            'unique_id'      => $ship->getUniqueId(),
            'drought'        => $ship->getDrought(),
            'length'         => $ship->getLength(),
            'width'          => $ship->getWidth(),
            'capacity'       => $ship->getCapacity(),
            'power_type'     => $ship->getPowerType(),
            'engine_power'   => $ship->getEnginePower(),
            'sail_max_heigh' => $ship->getSailMaxHeigh(),
            'sail_count'     => $ship->getSailCount(),
            'date_creation'  => $ship->getDateCreation()->format('Y-m-d')
        ]);
    }

    /**
     * Deletes a ship entity.
     *
     * @Rest\Delete("/ship/delete/{shipId}", name="ship_delete")
     *
     * @RequestParam(
     *   name="shipId",
     *   requirements="^[1-9][0-9]*",
     *   description="ship id",
     *   strict=true,
     *   nullable=false
     * )
     */
    public function deleteAction(int $shipId): JsonResponse
    {
        $em     = $this->getDoctrine()->getManager();
        $ship   = $em->getRepository('AppBundle:Ship')->find($shipId);

        if(null === $ship) {
            return new JsonResponse(['message' => 'Ship not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($ship);
        $em->flush();

        return new JsonResponse(['message' => 'deletion successfull']);
    }
}
