<?php

declare(strict_types=1);

namespace App\Document;

use App\Contract\Document\EmbeddedDocumentInterface;

class Speech implements EmbeddedDocumentInterface
{

    /**
     * @var string
     */
    protected $topic;

    /**
     * @var string
     */
    protected $speaker;

    /**
     * @var int
     * Start time in minutes from the beginning of the day
     * (e.g. 0=midnight, 60=1am, 375=6.15pm)
     */
    protected $startTime;

    /**
     * @var int
     * End time in minutes from the beginning of the day
     * (e.g. 0=midnight, 60=1am, 375=6.15pm)
     */
    protected $endTime;


    public function __construct()
    {
        $this->topic = '';
        $this->speaker = '';
        $this->startTime = 0;
        $this->endTime = 1;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;
        return $this;
    }

    public function getSpeaker(): string
    {
        return $this->speaker;
    }

    public function setSpeaker(string $speaker): self
    {
        $this->speaker = $speaker;
        return $this;
    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): int
    {
        return $this->endTime;
    }

    public function setEndTime(int $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }
}
