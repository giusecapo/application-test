<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SecurityController extends AbstractController
{
    public function login(): JsonResponse
    {
        //Returning an empty JsonResponse works here because
        //the response is intercepted and completed with data by symfony
        return new JsonResponse();
    }
}
