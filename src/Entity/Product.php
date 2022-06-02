<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups; 
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
/**
 * @ApiResource
 */
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /**
     * @Groups({"product:read"})
     */
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    /**
     * @Groups({"product:read"})
     * @Assert\NotBlank(message="Le titre est obligatoire")
     * @Assert\Length(min=3, minMessage = "Le titre doit faire au moins {{ limit }} caractères")
     */
    private $name;

    #[ORM\Column(type: 'text')]
    /**
     * @Groups({"product:read"})
     * @Assert\NotBlank(message="La description est obligatoire")
     * @Assert\Length(min=10, minMessage="La description doit faire au moins {{ limit }} caractères")
     */
    private $description;

    #[ORM\Column(type: 'integer')]
    /**
     * @Groups({"product:read"})
     * @Assert\NotBlank(message="Le prix est obligatoire")
     */
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
