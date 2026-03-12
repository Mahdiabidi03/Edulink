<?php

namespace App\Tests;

use App\Entity\Note;
use App\Service\NoteManager;
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
    private NoteManager $manager;

    protected function setUp(): void
    {
        $this->manager = new NoteManager();
    }

    public function testValidNote(): void
    {
        $note = new Note();
        $note->setTitle("My First Note");
        $note->setContent("Learning Symfony is fun.");

        $this->assertTrue($this->manager->validate($note));
    }

    public function testEmptyTitleThrowsException(): void
    {
        $note = new Note();
        $note->setTitle("");
        $note->setContent("Some content.");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Note Title is mandatory.");

        $this->manager->validate($note);
    }

    public function testEmptyContentThrowsException(): void
    {
        $note = new Note();
        $note->setTitle("Important Note");
        $note->setContent("");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Note Content is mandatory.");

        $this->manager->validate($note);
    }
}
