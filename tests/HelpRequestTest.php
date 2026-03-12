<?php

namespace App\Tests;

use App\Entity\HelpRequest;
use App\Service\HelpRequestManager;
use PHPUnit\Framework\TestCase;

class HelpRequestTest extends TestCase
{
    private HelpRequestManager $manager;

    protected function setUp(): void
    {
        $this->manager = new HelpRequestManager();
    }

    public function testValidHelpRequest(): void
    {
        $helpRequest = new HelpRequest();
        $helpRequest->setDescription("I need help with my Symfony project.");
        $helpRequest->setBounty(50);

        $this->assertTrue($this->manager->validate($helpRequest));
    }

    public function testEmptyDescriptionThrowsException(): void
    {
        $helpRequest = new HelpRequest();
        $helpRequest->setDescription("");
        $helpRequest->setBounty(50);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Description cannot be empty.");

        $this->manager->validate($helpRequest);
    }

    public function testNegativeBountyThrowsException(): void
    {
        $helpRequest = new HelpRequest();
        $helpRequest->setDescription("Help needed.");
        $helpRequest->setBounty(-10);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Bounty offered cannot be negative.");

        $this->manager->validate($helpRequest);
    }
}
