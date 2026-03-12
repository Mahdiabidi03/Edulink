<?php

namespace App\Tests;

use App\Entity\Challenge;
use App\Service\ChallengeManager;
use PHPUnit\Framework\TestCase;

class ChallengeTest extends TestCase
{
    private ChallengeManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ChallengeManager();
    }

    public function testValidChallenge(): void
    {
        $challenge = new Challenge();
        $challenge->setTitle("PHP Mastery");
        $challenge->setRewardPoints(500);

        $this->assertTrue($this->manager->validate($challenge));
    }

    public function testEmptyTitleThrowsException(): void
    {
        $challenge = new Challenge();
        $challenge->setTitle("");
        $challenge->setRewardPoints(500);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Challenge Title cannot be empty.");

        $this->manager->validate($challenge);
    }

    public function testNonPositiveRewardPointsThrowsException(): void
    {
        $challenge = new Challenge();
        $challenge->setTitle("PHP Mastery");
        $challenge->setRewardPoints(0);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Reward Points must be strictly positive (> 0).");

        $this->manager->validate($challenge);
    }
}
