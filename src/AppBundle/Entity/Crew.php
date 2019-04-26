<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * Crew
 * @ORM\Table(name="crew")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CrewRepository")
 */
class Crew
{
    /**
     * @var int
     * @SWG\Property(description="The unique identifier of the crew.")
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @SWG\Property(type="string", maxLength=255, description="Firstname of the crew")
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     * @SWG\Property(type="string", maxLength=255, description="Lastname of the crew")
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var \DateTime
     * @SWG\Property(type="datetime", description="Birthdate of the crew")
     * @ORM\Column(name="birth_date", type="datetime")
     */
    private $birthDate;

    /**
     * @var Job
     * @SWG\Property(type="integer", description="job id of the crew")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Job", inversedBy="workers")
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id", nullable=false)
     */
    private $job;

    /**
     * @var Ship|null
     * @SWG\Property(type="integer", description="ship id of crew working in")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ship", inversedBy="crewMembers")
     * @ORM\JoinColumn(name="ship_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $ship;


    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Crew
     */
    public function setFirstName(string $firstname): Crew
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Crew
     */
    public function setLastName(string $lastname): Crew
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastname;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return Crew
     */
    public function setBirthDate(\DateTime $birthDate): Crew
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate(): \DateTime
    {
        return $this->birthDate;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setJob(Job $job): Crew
    {
        $this->job = $job;

        return $this;
    }

    public function getShip(): ?Ship
    {
        return $this->ship;
    }

    public function setShip(?Ship $ship): Crew
    {
        $this->ship = $ship;

        return $this;
    }
}

