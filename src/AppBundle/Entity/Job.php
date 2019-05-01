<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Job
 *
 * @ORM\Table(name="job")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JobRepository")
 */
class Job
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"list", "detail"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"list", "detail"})
     */
    private $name;

    /**
     * @var Crew[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Crew", mappedBy="job")
     */
    private $workers;

    /**
     * @var Ship[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ship", mappedBy="job")
     */
    private $ships;

    /**
     * @var Harbor[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Harbor", mappedBy="jobs", cascade="persist")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * 
     */
    private $harbors;

    public function __construct()
    {
        $this->workers = new ArrayCollection();
        $this->ships   = new ArrayCollection();
        $this->harbors = new ArrayCollection();
    }


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
     * Set name.
     *
     * @param string $name
     *
     * @return Job
     */
    public function setName(string $name): Job
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getWorkers(): Collection
    {
        return $this->workers;
    }

    public function addHarbor(Harbor $harbor): Job
    {
        $this->harbors[] = $harbor;

        return $this;
    }

    public function getHarbors(): Collection
    {
        return $this->harbors;
    }

    public function getShips(): Collection
    {
        return $this->ships;
    }
}
