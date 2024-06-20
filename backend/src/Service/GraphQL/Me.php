<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Contract\Document\UserInterface;

final class Me
{

    private ?UserInterface $user;

    private bool $authenticated;


    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function setAuthenticated(bool $authenticated): self
    {
        $this->authenticated = $authenticated;

        return $this;
    }
}
