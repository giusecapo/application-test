<?php

declare(strict_types=1);

namespace App\Service\Security;

final class Credentials
{

    private string $username;

    private string $password;


    public function __construct()
    {
        $this->username = '';
        $this->password = '';
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Credentials
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): Credentials
    {
        $this->password = $password;

        return $this;
    }
}
