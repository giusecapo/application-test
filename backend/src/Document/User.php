<?php

declare(strict_types=1);

namespace App\Document;

use \DomainException as DomainException;
use Symfony\Component\Security\Core\User\EquatableInterface;
use App\Utility\ArrayValidator;
use App\Contract\Document\ConcurrencySafeDocumentInterface;
use App\Contract\Document\UserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use App\DocumentModel\UserModel;
use App\Service\Constant\ExceptionCodes;
use App\Security\Constant\Roles;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

class User extends AbstractConcurrencySafeDocument implements
    ConcurrencySafeDocumentInterface,
    EquatableInterface,
    LegacyPasswordAuthenticatedUserInterface,
    UserInterface,
    SymfonyUserInterface
{

    /**
     * @var string|null
     */
    protected $username;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * @var string|null
     */
    protected $unhashedPassword;

    /**
     * @var string|null
     */
    protected $currentUnhashedPassword;

    /**
     * @var string|null
     */
    protected $salt;

    /**
     * @var array
     */
    protected $roles;


    public function __construct()
    {
        $this->username = null;
        $this->password = null;
        $this->unhashedPassword = null;
        $this->currentUnhashedPassword = null;
        $this->salt = null;
        //ROLE_USER is the default role for each user 
        //and is required by symfony authentication and authorization system.
        $this->roles = array(Roles::ROLE_USER);
    }


    /**
     * @inheritDoc
     */
    public static  function getDocumentModelName(): string
    {
        return UserModel::class;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName(): string
    {
        return User::class;
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return $this->username ?? '';
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @throws DomainException when the salt is not already set
     */
    public function setPassword(?string $password): self
    {
        if (!isset($this->salt)) {
            throw new DomainException(
                'Set a salt with User->setSalt() before setting the password',
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }

        $this->password = $password;

        return $this;
    }

    public function getCurrentUnhashedPassword(): ?string
    {
        return $this->currentUnhashedPassword;
    }

    public function setCurrentUnhashedPassword(?string $currentUnhashedPassword): self
    {
        $this->currentUnhashedPassword = $currentUnhashedPassword;

        return $this;
    }

    public function getUnhashedPassword(): ?string
    {
        return $this->unhashedPassword;
    }

    public function setUnhashedPassword(?string $unhashedPassword): self
    {
        $this->unhashedPassword = $unhashedPassword;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        //The id property is set by the ORM when the object is persisted.
        //If the id is different than null, then the object was loaded from storage
        //and the salt must not be updated!
        if (isset($this->id)) {
            throw new DomainException(
                'The salt can only be set once on object creation and should never change.',
                ExceptionCodes::DOMAIN_EXCEPTION
            );
        }

        $this->salt = $salt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {
        $this->unhashedPassword = '';
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;
        $this->roles = array_unique($this->roles);

        return $this;
    }

    public function removeRole(string $role): self
    {
        $roleKey = array_search($role, $this->roles, true);
        if ($roleKey !== false) {
            unset($this->roles[$roleKey]);
        }

        $this->roles = array_values($this->roles);

        return $this;
    }

    /**
     * @inheritDoc
     * @param SymfonyUserInterface|UserInterface $user
     */
    public function isEqualTo(SymfonyUserInterface $user): bool
    {
        //Do not compare the passwords of the deserialized user object (from the TokenStorage) 
        // to the password of the refreshed user object (from storage)!
        //Before serialization the method eraseCredentials() is called, which resets the password.
        return $user->getId() === $this->id
            && $user->getUserIdentifier() === $this->username
            && ArrayValidator::isEqualTo($user->getRoles(), $this->roles);
    }
}
