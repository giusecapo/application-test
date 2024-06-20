<?php

declare(strict_types=1);

namespace App\DocumentModel;

use App\Contract\DocumentModel\AugmentedDocumentDocumentModelInterface;
use App\Document\User;
use App\Contract\DocumentModel\DocumentModelInterface;
use App\Contract\DocumentModel\MappableDocumentDocumentModelInterface;
use App\Contract\DocumentModel\Mapper\MapperInterface;
use App\Contract\DocumentModel\Repository\RepositoryInterface;
use App\Contract\DocumentModel\Writer\WriterInterface;
use App\DocumentModel\Mapper\UserMapper;
use App\DocumentModel\Repository\UserRepository;
use App\Service\Document\ReadWriteHelper;
use App\DocumentModel\Writer\UserWriter;
use App\Security\Constant\Roles;
use App\Service\Document\WriteManager;
use App\Service\Security\EncryptionProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;

final class UserModel implements
    DocumentModelInterface,
    MappableDocumentDocumentModelInterface,
    AugmentedDocumentDocumentModelInterface
{

    public const DOCUMENT_NAME = User::class;

    public function __construct(
        private UserRepository $userRepository,
        private UserMapper $userMapper,
        private UserWriter $userWriter,
        private ReadWriteHelper $readWriteHelper,
        private WriteManager $writeManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EncryptionProvider $encryptionProvider,
        private LoggerInterface $logger,
        private string $appSecret
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
     * @return UserMapper
     */
    public function getMapper(): MapperInterface
    {
        return $this->userMapper;
    }

    /**
     * @inheritDoc
     * @return UserRepository
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->userRepository;
    }

    /**
     * @inheritDoc
     * @return UserWriter
     */
    public function getWriter(): WriterInterface
    {
        return $this->userWriter;
    }

    /**
     * @inheritDoc
     */
    public function getWriteManager(): WriteManager
    {
        return $this->writeManager;
    }

    public function create(
        User $user,
        ?array $validationGroups = null,
        bool $checkAuthorization = true
    ): void {

        //Make sure each user has at least ROLE_USER
        $user->addRole(Roles::ROLE_USER);

        //If a password is not set, generate a random secure password.
        if ($user->getUnhashedPassword() === null) {
            $user->setUnhashedPassword($this->generateRandomSecurePassword());
        }

        $this->hashPassword($user);

        $this->readWriteHelper->create($user, $validationGroups, $checkAuthorization);
    }

    public function update(
        User $user,
        ?array $validationGroups,
        int $expectedVersion,
        bool $checkAuthorization = true
    ): void {

        //Make sure each user has at least ROLE_USER
        $user->addRole(Roles::ROLE_USER);

        //Update the password hash
        if ($user->getUnhashedPassword() !== null) {
            $this->hashPassword($user);
        }

        $this->readWriteHelper->update(
            $user,
            $validationGroups,
            $expectedVersion,
            $checkAuthorization,
            $this->userMapper
        );
    }

    public function delete(
        User $user,
        int $expectedVersion,
        bool $checkAuthorization = true
    ): void {
        $this->readWriteHelper->delete($user, $expectedVersion, $checkAuthorization);
    }

    //=============================
    //      HELPER METHODS
    //=============================

    private function generateRandomSecurePassword(): string
    {
        return bin2hex(random_bytes(14));
    }

    private function hashPassword(User $user): void
    {
        //set salt only once!
        //the salt must never change
        if ($user->getSalt() === null) {
            $user->setSalt(bin2hex(random_bytes(32)));
        }
        $password = $this->userPasswordHasher->hashPassword($user, $user->getUnhashedPassword());
        $user->setPassword($password);
    }
}
