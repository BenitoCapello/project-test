<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Harbor
 *
 * @ORM\Table(name="harbor")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HarborRepository")
 */
class Harbor
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="drought_allowed", type="float")
     */
    private $droughtAllowed;

    /**
     * @var int
     *
     * @ORM\Column(name="max_allowed_length", type="integer")
     */
    private $maxAllowedLength;

    /**
     * @var int
     *
     * @ORM\Column(name="max_allowed_width", type="integer")
     */
    private $maxAllowedWidth;

    /**
     * @var int
     *
     * @ORM\Column(name="accommodation_capacity", type="integer")
     */
    private $accommodationCapacity;

    /**
     * @var Job[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Job", mappedBy="harbors", cascade="persist")
     * @ORM\JoinTable(name="harbor_jobs", 
     *  joinColumns={
     *      @ORM\JoinColumn(name="harbor_id", referencedColumnName="id", onDelete="CASCADE")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     *  }
     * )
     */
    private $jobs;

    /**
     * @var Travel[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Travel", mappedBy="harborDeparture", orphanRemoval=true)
     */
    private $departureHistory;

    /**
     * @var Travel[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Travel", mappedBy="harborArival", orphanRemoval=true)
     */
    private $arivalHistory;

    public function __construct()
    {
        $this->jobs             = new ArrayCollection();
        $this->departureHistory = new ArrayCollection();
        $this->arivalHistory    = new ArrayCollection();
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
     * @return Harbor
     */
    public function setName(string $name): Harbor
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

    /**
     * Set droughtAllowed.
     *
     * @param float $droughtAllowed
     *
     * @return Harbor
     */
    public function setDroughtAllowed(float $droughtAllowed): Harbor
    {
        $this->droughtAllowed = $droughtAllowed;

        return $this;
    }

    /**
     * Get droughtAllowed.
     *
     * @return float
     */
    public function getDroughtAllowed(): float
    {
        return $this->droughtAllowed;
    }

    /**
     * Set maxAllowedLength.
     *
     * @param int $maxAllowedLength
     *
     * @return Harbor
     */
    public function setMaxAllowedLength(int $maxAllowedLength): Harbor
    {
        $this->maxAllowedLength = $maxAllowedLength;

        return $this;
    }

    /**
     * Get maxAllowedLength.
     *
     * @return int
     */
    public function getMaxAllowedLength(): int
    {
        return $this->maxAllowedLength;
    }

    /**
     * Set maxAllowedWidth.
     *
     * @param int $maxAllowedWidth
     *
     * @return Harbor
     */
    public function setMaxAllowedWidth(int $maxAllowedWidth): Harbor
    {
        $this->maxAllowedWidth = $maxAllowedWidth;

        return $this;
    }

    /**
     * Get maxAllowedWidth.
     *
     * @return int
     */
    public function getMaxAllowedWidth(): int
    {
        return $this->maxAllowedWidth;
    }

    /**
     * Set accommodationCapacity.
     *
     * @param int $accommodationCapacity
     *
     * @return Harbor
     */
    public function setAccommodationCapacity(int $accommodationCapacity): Harbor
    {
        $this->accommodationCapacity = $accommodationCapacity;

        return $this;
    }

    /**
     * Get accommodationCapacity.
     *
     * @return int
     */
    public function getAccommodationCapacity(): int
    {
        return $this->accommodationCapacity;
    }

    // not working yet
    public function addJobs(array $jobs): Harbor
    {
        foreach ($jobs as $job) {
            if (!$this->jobs->contains($job)) {
                $this->jobs->add($job);
                $job->addHarbor($this);
            }
        }

        return $this;
    }

    public function removeJob(Job $job): Harbor
    {
        $this->jobs->removeElement($job);

        return $this;
    }

    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function getDepartureHistory(): Collection
    {
        return $this->departureHistory;
    }

    public function getArivalHistory(): Collection
    {
        return $this->arivalHistory;
    }
}
