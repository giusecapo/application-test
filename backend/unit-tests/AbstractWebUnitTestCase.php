<?php

declare(strict_types=1);

namespace App\UnitTests;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebUnitTestCase extends WebTestCase
{

    protected string $domain = 'https://www.application.test';

    protected string $graphqlUrl = 'https://www.application.test/graphql';


    protected function login(KernelBrowser $client, string $username): void
    {
        $documentManager = self::getContainer()->get(DocumentManager::class);
        $user = $documentManager
            ->createQueryBuilder(User::class)
            ->field('username')->equals($username)
            ->readOnly()
            ->getQuery()
            ->getSingleResult();

        $client->loginUser($user, 'stateful');
    }
}
