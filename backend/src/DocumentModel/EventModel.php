<?php

declare(strict_types=1);

namespace App\DocumentModel;

use App\Contract\DocumentModel\AugmentedDocumentDocumentModelInterface;
use App\Document\Event;
use App\Contract\DocumentModel\DocumentModelInterface;
use App\Contract\DocumentModel\MappableDocumentDocumentModelInterface;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Contract\DocumentModel\Writer\WriterInterface;
use App\DocumentModel\Mapper\EventMapper;
use App\DocumentModel\Repository\EventRepository;
use App\Service\Document\ReadWriteHelper;
use App\DocumentModel\Writer\EventWriter;
use App\Service\Constant\ExceptionCodes;
use App\Security\Constant\Roles;
use App\Service\Document\WriteManager;
use App\Service\Security\EncryptionProvider;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\EventPasswordHasherInterface;
use \Exception as Exception;
use Psr\Log\LoggerInterface;
use \RuntimeException as RuntimeException;

final class EventModel implements
    DocumentModelInterface,
    MappableDocumentDocumentModelInterface,
    AugmentedDocumentDocumentModelInterface
{

    public const DOCUMENT_NAME = Event::class;

    public function __construct(
        private EventRepository $eventRepository,
        private EventMapper $eventMapper,
        private EventWriter $eventWriter,
        private ReadWriteHelper $readWriteHelper,
        private WriteManager $writeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return self::DOCUMENT_NAME;
    }

    /**
     * @inheritDoc
     * @return EventMapper
     */
    public function getMapper(): MapperInterface
    {
        return $this->eventMapper;
    }

    /**
     * @inheritDoc
     * @return EventRepository
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->eventRepository;
    }

    /**
     * @inheritDoc
     * @return EventWriter
     */
    public function getWriter(): WriterInterface
    {
        return $this->eventWriter;
    }

    /**
     * @inheritDoc
     */
    public function getWriteManager(): WriteManager
    {
        return $this->writeManager;
    }

    public function create(
        Event $event,
        ?array $validationGroups = null,
        bool $checkAuthorization = true
    ): void {
        $this->readWriteHelper->create($event, $validationGroups, $checkAuthorization);
    }

    public function update(
        Event $event,
        ?array $validationGroups,
        int $expectedVersion,
        bool $checkAuthorization = true
    ): void {
        $this->readWriteHelper->update(
            $event,
            $validationGroups,
            $expectedVersion,
            $checkAuthorization,
            $this->eventMapper
        );
    }

    public function delete(
        Event $event,
        int $expectedVersion,
        bool $checkAuthorization = true
    ): void {
        $this->readWriteHelper->delete($event, $expectedVersion, $checkAuthorization);
    }
}
