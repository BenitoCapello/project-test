<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Ship
 *
 * @ORM\Table(name="ship")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ShipRepository")
 */
class Ship
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    const POWER_TYPE_SAIL   = 0; //sail
    const POWER_TYPE_ENGINE = 1; //engine
    const POWER_TYPE_BOTH   = 2; //both

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="unique_id", type="string", length=255, unique=true)
     */
    private $uniqueId;

    /**
     * @var float
     *
     * @ORM\Column(name="drought", type="float")
     */
    private $drought;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="float")
     */
    private $length;

    /**
     * @var float
     *
     * @ORM\Column(name="width", type="float")
     */
    private $width;

    /**
     * @var int|1
     *
     * @ORM\Column(name="capacity", type="integer")
     */
    private $capacity;

    /**
     * @var int
     *
     * @ORM\Column(name="power_type", type="integer")
     */
    private $powerType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="engine_power", type="integer", nullable=true)
     */
    private $enginePower;

    /**
     * @var float|null
     *
     * @ORM\Column(name="sail_max_heigh", type="float", nullable=true)
     */
    private $sailMaxHeigh;

    /**
     * @var int|null
     *
     * @ORM\Column(name="sail_count", type="integer", nullable=true)
     */
    private $sailCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var Harbor
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Harbor")
     * @ORM\JoinColumn(nullable=false)
     */
    private $harborBuilt;

    /**
     * @var Job
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Job", inversedBy="ships")
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id", nullable=false)
     */
    private $job;

    /**
     * @var Crew[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Crew", mappedBy="ship")
     */
    private $crewMembers;

    /**
     * @var Travel[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Travel", mappedBy="ship", orphanRemoval=true)
     */
    private $travelHistory;

    public function __construct()
    {
        $this->crewMembers   = new ArrayCollection();
        $this->travelHistory = new ArrayCollection();
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
     * @return Ship
     */
    public function setName(string $name): Ship
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
     * Set uniqueId.
     *
     * @param string $uniqueId
     *
     * @return Ship
     */
    public function setUniqueId(string $uniqueId): Ship
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    /**
     * Get uniqueId.
     *
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * Set drought.
     *
     * @param float $drought
     *
     * @return Ship
     */
    public function setDrought(float $drought): Ship
    {
        $this->drought = $drought;

        return $this;
    }

    /**
     * Get drought.
     *
     * @return float
     */
    public function getDrought(): float
    {
        return $this->drought;
    }

    /**
     * Set length.
     *
     * @param float $length
     *
     * @return Ship
     */
    public function setLength(float $length): Ship
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length.
     *
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * Set width.
     *
     * @param float $width
     *
     * @return Ship
     */
    public function setWidth(float $width): Ship
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     *
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * Set capacity.
     *
     * @param int $capacity
     *
     * @return Ship
     */
    public function setCapacity(int $capacity): Ship
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity.
     *
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * Set powerType.
     *
     * @param int $powerType
     *
     * @return Ship
     */
    public function setPowerType(int $powerType): Ship
    {
        $this->powerType = $powerType;

        return $this;
    }

    /**
     * Get powerType.
     *
     * @return int
     */
    public function getPowerType(): int
    {
        return $this->powerType;
    }

    /**
     * Set enginePower.
     *
     * @param int|null $enginePower
     *
     * @return Ship
     */
    public function setEnginePower($enginePower = null): Ship
    {
        $this->enginePower = $enginePower;

        return $this;
    }

    /**
     * Get enginePower.
     *
     * @return int|null
     */
    public function getEnginePower()
    {
        return $this->enginePower;
    }

    /**
     * Set sailMaxHeigh.
     *
     * @param float|null $sailMaxHeigh
     *
     * @return Ship
     */
    public function setSailMaxHeigh($sailMaxHeigh = null): Ship
    {
        $this->sailMaxHeigh = $sailMaxHeigh;

        return $this;
    }

    /**
     * Get sailMaxHeigh.
     *
     * @return float|null
     */
    public function getSailMaxHeigh()
    {
        return $this->sailMaxHeigh;
    }

    /**
     * Set sailCount.
     *
     * @param int|null $sailCount
     *
     * @return Ship
     */
    public function setSailCount($sailCount = null): Ship
    {
        $this->sailCount = $sailCount;

        return $this;
    }

    /**
     * Get sailCount.
     *
     * @return int|null
     */
    public function getSailCount()
    {
        return $this->sailCount;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Ship
     */
    public function setDateCreation(\DateTime $dateCreation): Ship
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation(): \DateTime
    {
        return $this->dateCreation;
    }


    public function getHarborBuilt(): Harbor
    {
        return $this->harborBuilt;
    }

    public function setHarborBuilt(Harbor $harbor): Ship
    {
        $this->harborBuilt = $harbor;

        return $this;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setJob(Job $job): Ship
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return Collection|Crew[]
     */
    public function getCrewMembers(): Collection
    {
        return $this->crewMembers;
    }

    /**
     * @return Collection|Travel[]
     */
    public function getTravelHistory(): Collection
    {
        return $this->travelHistory;
    }

    public function addTravel(Travel ...$travelHistory): Ship
    {
        foreach ($travelHistory as $travel) {
            if (!$this->travelHistory->contains($travel)) {
                $this->travelHistory->add($travel);
            }
        }

        return $this;
    }
}
