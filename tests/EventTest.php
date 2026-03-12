<?php

namespace App\Tests;

use App\Entity\Event;
use App\Service\EventManager;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    private EventManager $manager;

    protected function setUp(): void
    {
        $this->manager = new EventManager();
    }

    public function testValidEvent(): void
    {
        $event = new Event();
        $event->setMaxCapacity(50);
        $event->setDateStart(new \DateTime('+1 day'));

        $this->assertTrue($this->manager->validate($event));
    }

    public function testNonPositiveCapacityThrowsException(): void
    {
        $event = new Event();
        $event->setMaxCapacity(0);
        $event->setDateStart(new \DateTime('+1 day'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Max Participants (Capacity) must be greater than 0.");

        $this->manager->validate($event);
    }

    public function testPastDateThrowsException(): void
    {
        $event = new Event();
        $event->setMaxCapacity(50);
        $event->setDateStart(new \DateTime('-1 day'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Event Date cannot be in the past.");

        $this->manager->validate($event);
    }
}
