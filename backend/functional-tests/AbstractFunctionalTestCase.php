<?php

declare(strict_types=1);

namespace App\FunctionalTests;

use App\Contract\Document\ConcurrencySafeDocumentInterface;
use App\Contract\Document\DocumentInterface;
use App\Document\User;
use App\Service\GlobalId\GlobalIdProvider;
use App\Service\GraphQL\FieldEncryptionProvider;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


abstract class AbstractFunctionalTestCase extends WebTestCase
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

    protected function getEncryptedVersion(ConcurrencySafeDocumentInterface $document): string
    {
        $fieldEncryptionProvider = self::getContainer()->get(FieldEncryptionProvider::class);
        return $fieldEncryptionProvider->encrypt((string)$document->getVersion(), $document->getId());
    }

    protected function toGlobalId(DocumentInterface $document): string
    {
        $globalIdProvider = self::getContainer()->get(GlobalIdProvider::class);
        return $globalIdProvider->toGlobalId($document);
    }

    protected function getIdFromGlobalId(string $globalId): string
    {
        $globalIdProvider = self::getContainer()->get(GlobalIdProvider::class);
        return $globalIdProvider->getIdFromGlobalId($globalId);
    }
}
