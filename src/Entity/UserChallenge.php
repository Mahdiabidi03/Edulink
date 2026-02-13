<?php

namespace App\Entity;

use App\Repository\UserChallengeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserChallengeRepository::class)]
class UserChallenge
{
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_PENDING     = 'PENDING';
    public const STATUS_COMPLETED   = 'COMPLETED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'La progression est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d+\/\d+$/',
        message: 'La progression doit être au format X/Y (ex: 2/3).'
    )]
    #[ORM\Column(length: 50)]
    private string $progress = '0/3';

    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: [
            self::STATUS_IN_PROGRESS,
            self::STATUS_PENDING,
            self::STATUS_COMPLETED
        ],
        message: 'Statut invalide.'
    )]
    #[ORM\Column(length: 50)]
    private string $status = self::STATUS_IN_PROGRESS;

    // ✅ Nouveau champ : fichier de preuve
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $proofFileName = null;

    #[ORM\ManyToOne(inversedBy: 'userChallenges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userChallenges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Challenge $challenge = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProgress(): string
    {
        return $this->progress;
    }

    public function setProgress(string $progress): static
    {
        $this->progress = $progress;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getProofFileName(): ?string
    {
        return $this->proofFileName;
    }

    public function setProofFileName(?string $proofFileName): static
    {
        $this->proofFileName = $proofFileName;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getChallenge(): ?Challenge
    {
        return $this->challenge;
    }

    public function setChallenge(?Challenge $challenge): static
    {
        $this->challenge = $challenge;
        return $this;
    }
}
