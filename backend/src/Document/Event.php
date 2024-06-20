<?php

declare(strict_types=1);

namespace App\Document;

use App\Contract\Document\ConcurrencySafeDocumentInterface;
use App\Contract\Document\EquatableDocumentInterface;
use App\DocumentModel\EventModel;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Event extends AbstractConcurrencySafeDocument implements
    ConcurrencySafeDocumentInterface,
    EquatableDocumentInterface
{

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $participants;

    /**
     * @var DateTimeInterface
     */
    protected $date;

    /**
     * @var Collection
     * A collection of Speech objects
     */
    protected $program;


    public function __construct()
    {
        $this->key = '';
        $this->name = '';
        $this->participants = [];
        $this->date = new DateTime();
        $this->program = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public static function getDocumentModelName(): string
    {
        return EventModel::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getDocumentName(): string
    {
        return Event::class;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function setParticipants(array $participants): self
    {
        $this->participants = $participants;
        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getProgram(): Collection
    {
        return $this->program;
    }

    public function setProgram(Collection $program): self
    {
        $this->program = $program;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEqualTo(EquatableDocumentInterface $document): bool
    {
        return $document instanceof Event
            && $this->key === $document->getKey();
    }
}
