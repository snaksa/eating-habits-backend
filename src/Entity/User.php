<?php declare (strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $username;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Meal", mappedBy="user")
     */
    private $meals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WaterSupply", mappedBy="user")
     */
    private $waterSupplies;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Weight", mappedBy="user")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $weights;

    public function __construct()
    {
        $this->meals = new ArrayCollection();
        $this->waterSupplies = new ArrayCollection();
        $this->weights = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }



    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return Collection|Meal[]
     */
    public function getMeals(): Collection
    {
        return $this->meals;
    }

    public function addMeal(Meal $meal): self
    {
        if (!$this->meals->contains($meal)) {
            $this->meals[] = $meal;
            $meal->setUser($this);
        }

        return $this;
    }

    public function removeMeal(Meal $meal): self
    {
        if ($this->meals->contains($meal)) {
            $this->meals->removeElement($meal);
            // set the owning side to null (unless already changed)
            if ($meal->getUser() === $this) {
                $meal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|WaterSupply[]
     */
    public function getWaterSupplies(): Collection
    {
        return $this->waterSupplies;
    }

    public function addWaterSupply(WaterSupply $waterSupply): self
    {
        if (!$this->waterSupplies->contains($waterSupply)) {
            $this->waterSupplies[] = $waterSupply;
            $waterSupply->setUser($this);
        }

        return $this;
    }

    public function removeWaterSupply(WaterSupply $waterSupply): self
    {
        if ($this->waterSupplies->contains($waterSupply)) {
            $this->waterSupplies->removeElement($waterSupply);
            // set the owning side to null (unless already changed)
            if ($waterSupply->getUser() === $this) {
                $waterSupply->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Weight[]
     */
    public function getWeights(): Collection
    {
        return $this->weights;
    }

    public function addWeight(Weight $weight): self
    {
        if (!$this->weights->contains($weight)) {
            $this->weights[] = $weight;
            $weight->setUser($this);
        }

        return $this;
    }

    public function removeWeight(Weight $weight): self
    {
        if ($this->weights->contains($weight)) {
            $this->weights->removeElement($weight);
            // set the owning side to null (unless already changed)
            if ($weight->getUser() === $this) {
                $weight->setUser(null);
            }
        }

        return $this;
    }
}
