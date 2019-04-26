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
use AppBundle\Service\StatService;
use Ramsey\Uuid\Uuid;

/**
 * Stat controller.
 *
 */
class StatController extends FOSRestController
{
    private $statService;

    public function __construct(StatService $statService)
    {
        $this->statService   = $statService;
    }

    /**
     * Counts amount of ships docked per harbor.
     * @Rest\Get("/stat/harbors/ship_docked", name="stat_harbors_ship_docked")
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
     *     description="Counts amount of ships docked per harbor."
     * )
     */
    public function harborShipsDockedAction(Request $request): JsonResponse
    {
        $formatedResponse = $this->statService->getHarborsShipCount(
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedResponse);
    }

    /**
     * Get Count Travel per date for ships.
     * @Rest\Get("/stat/ship/travel/count", name="ship_travel_count_index")
     *
     * @QueryParam(
     *   name="shipId",
     *   default=null,
     *   description="null for all ships, else put a ship id in there boy",
     *   requirements="^[1-9][0-9]*",
     *   nullable=true
     * )
     * 
     * @QueryParam(
     *   name="date",
     *   default=null,
     *   description="date format (yyyy or yyyy-mm or yyyy-mm-dd)",
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="groupType",
     *   default="day",
     *   description="wether you want to group by day, month or year",
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
     *     description="count travel"
     *     )
     * )
     */
    public function shipTravelCountAction(Request $request): JsonResponse
    {
        $shipId    = ('' === $request->query->get('shipId')) ? null : $request->query->get('shipId');
        $date      = ('' === $request->query->get('date')) ? null : $request->query->get('date');
        $groupType = ('' === $request->query->get('groupType')) ? 'day' : $request->query->get('groupType');

        $formatedResponse = $this->statService->getShipTravelCountPerDate(
            $shipId,
            $date,
            $groupType,
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedResponse);
    }

    /**
     * Get Count Travel per date for harbors.
     * @Rest\Get("/stat/harbor/travel/count", name="harbor_travel_count_index")
     *
     * @QueryParam(
     *   name="harborId",
     *   default=null,
     *   description="null for all harbors, else put an harbor id in there boy",
     *   requirements="^[1-9][0-9]*",
     *   nullable=true
     * )
     * 
     * @QueryParam(
     *   name="date",
     *   default=null,
     *   description="date format (yyyy or yyyy-mm or yyyy-mm-dd)",
     *   nullable=true
     * )
     *
     * @QueryParam(
     *   name="groupType",
     *   default="day",
     *   description="wether you want to group by day, month or year",
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
     *     description="count travel"
     *     )
     * )
     */
    public function harborTravelCountAction(Request $request): JsonResponse
    {
        $harborId  = ('' === $request->query->get('harborId')) ? null : $request->query->get('harborId');
        $date      = ('' === $request->query->get('date')) ? null : $request->query->get('date');
        $groupType = ('' === $request->query->get('groupType')) ? 'day' : $request->query->get('groupType');

        $formatedResponse = $this->statService->getHarborTravelCountPerDate(
            $harborId,
            $date,
            $groupType,
            $request->query->get('page'),
            $request->query->get('limit')
        );

        return new JsonResponse($formatedResponse);
    }
}
