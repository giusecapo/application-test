<?php

declare(strict_types=1);

namespace App\Contract\Document;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends
    SymfonyUserInterface,
    DocumentInterface,
    ConcurrencySafeDocumentInterface
{

    public function getId(): ?string;
}
