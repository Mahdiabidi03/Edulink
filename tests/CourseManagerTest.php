<?php

namespace App\Tests;

use App\Entity\Cours;
use App\Service\CourseManager;
use PHPUnit\Framework\TestCase;

class CourseManagerTest extends TestCase
{
    private CourseManager $manager;

    protected function setUp(): void
    {
        $this->manager = new CourseManager();
    }

    public function testValidCourse(): void
    {
        $cours = new Cours();
        $cours->setTitle("Unit Testing 101");
        $cours->setPricePoints(100);

        $this->assertTrue($this->manager->validate($cours));
    }

    public function testEmptyTitleThrowsException(): void
    {
        $cours = new Cours();
        $cours->setTitle("");
        $cours->setPricePoints(100);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Course Title cannot be empty.");

        $this->manager->validate($cours);
    }

    public function testNegativePriceThrowsException(): void
    {
        $cours = new Cours();
        $cours->setTitle("Valid Title");
        $cours->setPricePoints(-50);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Price (Points) cannot be negative.");

        $this->manager->validate($cours);
    }
}
