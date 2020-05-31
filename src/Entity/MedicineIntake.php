<?php declare (strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="medicines_intake")
 * @ORM\Entity(repositoryClass="App\Repository\MedicineIntakeRepository")
 */
class MedicineIntake
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MedicineSchedule", inversedBy="intakes")
     * @ORM\JoinColumn(name="medicine_schedule_id", referencedColumnName="id", nullable=false)
     */
    private MedicineSchedule $medicineSchedule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMedicineSchedule(): ?MedicineSchedule
    {
        return $this->medicineSchedule;
    }

    public function setMedicineSchedule(MedicineSchedule $medicineSchedule): self
    {
        $this->medicineSchedule = $medicineSchedule;

        return $this;
    }
}
