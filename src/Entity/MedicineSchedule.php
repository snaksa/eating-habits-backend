<?php declare (strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="medicines_schedule")
 * @ORM\Entity(repositoryClass="App\Repository\MedicineScheduleRepository")
 */
class MedicineSchedule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private \DateTimeInterface $intake_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $period_span = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Medicine", inversedBy="schedule")
     * @ORM\JoinColumn(name="medicine_id", referencedColumnName="id", nullable=false)
     */
    private Medicine $medicine;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MedicineIntake", mappedBy="medicineSchedule")
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $intakes;

    public function __construct()
    {
        $this->intakes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getIntakeTime(): ?\DateTimeInterface
    {
        return $this->intake_time;
    }

    public function setIntakeTime(\DateTimeInterface $time): self
    {
        $this->intake_time = $time;

        return $this;
    }

    public function getMedicine(): ?Medicine
    {
        return $this->medicine;
    }

    public function setMedicine(Medicine $medicine): self
    {
        $this->medicine = $medicine;

        return $this;
    }

    public function getPeriodSpan(): ?int
    {
        return $this->period_span;
    }

    public function setPeriodSpan(?int $period_span): self
    {
        $this->period_span = $period_span;

        return $this;
    }

    /**
     * @return Collection|MedicineIntake[]
     */
    public function getIntakes(): Collection
    {
        return $this->intakes;
    }

    public function addIntake(MedicineIntake $intake): self
    {
        if (!$this->intakes->contains($intake)) {
            $this->intakes[] = $intake;
            $intake->setMedicineSchedule($this);
        }

        return $this;
    }

    public function removeIntake(MedicineIntake $intake): self
    {
        if ($this->intakes->contains($intake)) {
            $this->intakes->removeElement($intake);
            // set the owning side to null (unless already changed)
            if ($intake->getMedicineSchedule() === $this) {
                $intake->setMedicineSchedule(null);
            }
        }

        return $this;
    }
}
