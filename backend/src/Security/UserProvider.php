<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Document\User;
use App\Service\QueryHelper\FiltersDescriptor;
use App\Service\QueryHelper\QueryCriteria;
use App\Service\QueryHelper\QueryHelper;
use App\Service\QueryHelper\UpdateDescriptor;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{

    public function __construct(private QueryHelper $queryHelper)
    {
    }

    /**
     * @inheritDoc
     */
    public function loadUserByIdentifier(string $username): UserInterface
    {
        return $this->loadUserByUsername($username);
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return UserInterface
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        // Load a User object from your data source or throw UserNotFoundException.
        // The $username argument may not actually be a username:
        // it is whatever value is being returned by the getUsername()
        // method in the User class.
        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->equals('username', $username);

        $queryCriteria = new QueryCriteria();
        $queryCriteria
            ->setFiltersDescriptor($filtersDescriptor)
            ->setFieldsToSelect(['id', 'version', 'username', 'password', 'salt', 'roles'])
            ->readOnly();

        /** @var User|null $user */
        $user = $this->queryHelper->getSingleResult(User::class, $queryCriteria);

        if (!isset($user)) {
            throw new UserNotFoundException("There is no user with the given username: $username");
        }

        return $user;
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->equals('id', $user->getId());

        $queryCriteria = new QueryCriteria();
        $queryCriteria
            ->setFiltersDescriptor($filtersDescriptor)
            ->setFieldsToSelect(['id', 'version', 'username', 'password', 'salt', 'roles'])
            ->readOnly();

        /** @var User|null $user */
        $user = $this->queryHelper->getSingleResult(User::class, $queryCriteria);

        if (!isset($user)) {
            throw new UserNotFoundException("The user does not exists");
        }

        return $user;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class): bool
    {
        return $class === User::class;
    }

    /**
     * upgradePassword: Upgrades the password hash of a user, typically for using a better hash algorithm.
     *
     * @param  UserInterface $user
     * @param  string $newHashedPassword
     * 
     * @return void
     */
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        // When passwords are in use, this method should:
        // 1. persist the new password in the persistent storage
        // 2. update the $user object with $user->setPassword($newHashedPassword);
        /** @var User $user */
        $user->setPassword($newHashedPassword);

        $filtersDescriptor = new FiltersDescriptor();
        $filtersDescriptor->equals('username', $user->getUserIdentifier());

        $updateDescriptor = new UpdateDescriptor();
        $updateDescriptor->set('password', $newHashedPassword);

        $queryCriteria = new QueryCriteria();
        $queryCriteria
            ->setFiltersDescriptor($filtersDescriptor)
            ->setUpdateDescriptor($updateDescriptor);

        $this->queryHelper->updateOneNow(User::class, $queryCriteria);
    }
}
