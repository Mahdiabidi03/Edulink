<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 120,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z].*/',
        message: 'Le titre doit commencer par une lettre majuscule.'
    )]
    #[ORM\Column(length: 120)]
    private ?string $title = null;

    #[Assert\NotBlank(message: 'L’objectif (goal) est obligatoire.')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'L’objectif doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'L’objectif ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 255)]
    private ?string $goal = null;

    #[Assert\NotNull(message: 'Les points de récompense sont obligatoires.')]
    #[Assert\PositiveOrZero(message: 'Les points doivent être un nombre positif ou zéro.')]
    #[ORM\Column]
    private ?int $rewardPoints = null;

    /**
     * @var Collection<int, UserChallenge>
     */
    #[ORM\OneToMany(
        mappedBy: 'challenge',
        targetEntity: UserChallenge::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $userChallenges;

    public function __construct()
    {
        $this->userChallenges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getGoal(): ?string
    {
        return $this->goal;
    }

    public function setGoal(string $goal): static
    {
        $this->goal = $goal;
        return $this;
    }

    public function getRewardPoints(): ?int
    {
        return $this->rewardPoints;
    }

    public function setRewardPoints(int $rewardPoints): static
    {
        $this->rewardPoints = $rewardPoints;
        return $this;
    }

    /**
     * @return Collection<int, UserChallenge>
     */
    public function getUserChallenges(): Collection
    {
        return $this->userChallenges;
    }

    public function addUserChallenge(UserChallenge $userChallenge): static
    {
        if (!$this->userChallenges->contains($userChallenge)) {
            $this->userChallenges->add($userChallenge);
            $userChallenge->setChallenge($this);
        }

        return $this;
    }

    public function removeUserChallenge(UserChallenge $userChallenge): static
    {
        if ($this->userChallenges->removeElement($userChallenge)) {
            if ($userChallenge->getChallenge() === $this) {
                $userChallenge->setChallenge(null);
            }
        }

        return $this;
    }
}
