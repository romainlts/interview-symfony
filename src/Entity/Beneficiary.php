<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BeneficiaryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
    #[Groups(['beneficiary:read', 'beneficiary:write'])]
    private $name;

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
}
