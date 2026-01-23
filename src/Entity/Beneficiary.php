<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BeneficiaryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    security: "is_granted('ROLE_USER')",
    normalizationContext: ['groups' => ['beneficiary:read']],
    denormalizationContext: ['groups' => ['beneficiary:write']]
)]
#[ORM\Entity(repositoryClass: BeneficiaryRepository::class)]
class Beneficiary
{
    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "integer")]
    #[Groups(['beneficiary:read'])] 
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: 'Name is required.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name must be at least {{ limit }} characters.',
        maxMessage: 'Name cannot exceed {{ limit }} characters.'
    )]
    #[Groups(['beneficiary:read', 'beneficiary:write'])]
    private $name;

    #[ORM\Column(type: "string", length: 180, nullable: true)]
    #[Groups(['beneficiary:read'])]
    private ?string $creatorEmail = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(['beneficiary:read'])]
    private ?\DateTimeImmutable $createdAt = null;    

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

    public function getCreatorEmail(): ?string
    {
        return $this->creatorEmail;
    }

    public function setCreatorEmail(string $creatorEmail): self
    {
        $this->creatorEmail = $creatorEmail;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
