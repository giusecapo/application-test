<?php

declare(strict_types=1);

namespace App\Service\Document;

use App\Contract\Document\DocumentInterface;
use App\Service\Constant\ExceptionCodes;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use \InvalidArgumentException as InvalidArgumentException;
use App\Utility\CollectionValidator;
use Doctrine\Common\Collections\Collection;
use \Exception as Exception;

/**
 * Document's crud events (create, update, delete) must be dispatched in different moments:
 * - after a document read
 * - before the unit of work is flushed (before sending DB commands)
 * - after the unit of work is flushed (if the DB commit was successful)
 * 
 * EventProvider provides the necessary methods to create and dispatch events and to 
 * buffer them during the whole transaction and dispatch them just before adn after the flush operation (before flush, after flush). 
 */
final class EventProvider
{
    public const ON_PRE_FLUSH_BUFFER = 'on_pre_flush_buffer';
    public const ON_POST_FLUSH_BUFFER = 'on_post_flush_buffer';

    private array $buffers;

    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        $this->buffers = array(
            static::ON_PRE_FLUSH_BUFFER => [],
            static::ON_POST_FLUSH_BUFFER => []
        );
    }


    public function dispatch(Event $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * Creates the event and dispatches it
     */
    public function createEventAndDispatch(string $eventName, DocumentInterface $document): void
    {
        $event = $this->createEvent($eventName, $document);
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * Creates one event per document and dispatches them 
     */
    public function createEventsAndDispatch(string $eventName, Collection $documents): void
    {
        if (!CollectionValidator::hasOnlyInstancesOfClass($documents, DocumentInterface::class)) {
            throw new InvalidArgumentException(
                'The $documents collection must contain only instances of App\Contract\Document\DocumentInterface.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
        $documents = $documents->toArray();
        foreach ($documents as $document) {
            $this->createEventAndDispatch($eventName, $document);
        }
    }

    /**
     * Adds an event to the specified buffer
     */
    public function addEventToBuffer(string $buffer, Event $event): void
    {
        if (!isset($this->buffers[$buffer])) {
            throw new InvalidArgumentException(
                sprintf("The provided value for \$buffer is not valid: %s.", $buffer),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $this->buffers[$buffer][] = $event;
    }

    /**
     * Creates an event and adds it to the specified buffer
     */
    public function createEventAndAddToBuffer(string $buffer, string $eventName, DocumentInterface $document): void
    {
        $event = $this->createEvent($eventName, $document);
        $this->addEventToBuffer($buffer, $event);
    }


    /**
     * Creates the events and adds them to the specified buffer
     */
    public function createEventsAndAddToBuffer(string $buffer, string $eventName, Collection $documents): void
    {
        if (!CollectionValidator::hasOnlyInstancesOfClass($documents, DocumentInterface::class)) {
            throw new InvalidArgumentException(
                'The $documents collection must contain only instances of App\Contract\Document\DocumentInterface.',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        $documents = $documents->toArray();
        foreach ($documents as $document) {
            $this->createEventAndAddToBuffer($buffer, $eventName, $document);
        }
    }

    /**
     * Creates the events for the specified document and adds them to the specified buffers
     */
    public function createEventAndAddToBuffers(array $bufferEventKeyValuePairs, DocumentInterface $document): void
    {
        foreach ($bufferEventKeyValuePairs as $buffer => $eventName) {
            $this->createEventAndAddToBuffer($buffer, $eventName, $document);
        }
    }

    /**
     * Creates the events for all the documents in the collection and adds them to the specified buffers
     */
    public function createEventsAndAddToBuffers(array $bufferEventKeyValuePairs, Collection $documents): void
    {
        foreach ($bufferEventKeyValuePairs as $buffer => $eventName) {
            $this->createEventsAndAddToBuffer($buffer, $eventName, $documents);
        }
    }


    /**
     * @internal do not use in business logic layer
     * Dispatches all the events int the specified buffer
     * 
     * @throws InvalidArgumentException if the buffer does not exist
     */
    public function dispatchBufferedEvents(string $buffer): void
    {
        if (!isset($this->buffers[$buffer])) {
            throw new InvalidArgumentException(
                sprintf("The provided value for \$buffer is not valid: %s.", $buffer),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }

        foreach ($this->buffers[$buffer] as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        //clear up the buffer
        $this->buffers[$buffer] = [];
    }

    private function createEvent(string $eventName, DocumentInterface $document): Event
    {
        try {
            $event = new $eventName($document);
            if (!$event instanceof Event) {
                throw new InvalidArgumentException(
                    'Expected $eventName to be the class name of a class 
                    extending from Symfony\\Contracts\\EventDispatcher\\Event, but it was not.',
                    ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
                );
            }
            return $event;
        } catch (Exception $exception) {
            throw new InvalidArgumentException(
                'An error occurred while creating the event to dispatch',
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION,
                $exception
            );
        }
    }
}
