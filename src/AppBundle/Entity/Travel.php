<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Travel
 *
 * @ORM\Table(name="travel")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TravelRepository")
 */
class Travel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="travel_date", type="datetime")
     */
    private $travelDate;

    /**
     * @var Ship
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ship", inversedBy="travelHistory")
     * @ORM\JoinColumn(name="ship_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $ship;

    /**
     * @var Harbor
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Harbor", inversedBy="departureHistory")
     * @ORM\JoinColumn(name="departure_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $harborDeparture;

    /**
     * @var Harbor
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Harbor", inversedBy="arivalHistory")
     * @ORM\JoinColumn(name="arival_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $harborArival;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set travelDate.
     *
     * @param \DateTime $travelDate
     *
     * @return Travel
     */
    public function setTravelDate(\DateTime $travelDate): Travel
    {
        $this->travelDate = $travelDate;

        return $this;
    }

    /**
     * Get travelDate.
     *
     * @return \DateTime
     */
    public function getTravelDate(): \DateTime
    {
        return $this->travelDate;
    }

    public function getShip(): Ship
    {
        return $this->ship;
    }

    public function setShip(Ship $ship): Travel
    {
        $this->ship = $ship;

        return $this;
    }

    public function getArborDeparture(): Harbor
    {
        return $this->harborDeparture;
    }

    public function setHarborDeparture(Harbor $harbor): Travel
    {
        $this->harborDeparture = $harbor;

        return $this;
    }

    public function getArborArival(): Harbor
    {
        return $this->harborArival;
    }

    public function setHarborArival(Harbor $harbor): Travel
    {
        $this->harborArival = $harbor;

        return $this;
    }
}
