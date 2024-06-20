<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\Constant\ExceptionCodes;
use App\Service\Security\Credentials;
use \Exception as Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\AuthenticatorInterface;

final class StatefulAuthenticator extends AbstractGuardAuthenticator implements AuthenticatorInterface
{
    /**
     * The name of the login route configured in router.yaml
     */
    private const LOGIN_ROUTE = 'login';


    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST')
            && $request->headers->get('content-type') === 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request)
    {
        try {
            $requestContent  = json_decode($request->getContent());
        } catch (Exception $exception) {
            throw new BadRequestHttpException('Invalid json document.', $exception, ExceptionCodes::BAD_REQUEST_EXCEPTION);
        }

        if (
            !isset($requestContent)
            || !isset($requestContent->username)
            || !isset($requestContent->password)
        ) {
            throw new BadRequestHttpException(
                'You must provide a valid json objet with username and password',
                null,
                ExceptionCodes::BAD_REQUEST_EXCEPTION
            );
        }

        $credentials = new Credentials();
        $credentials
            ->setUsername($requestContent->username)
            ->setPassword($requestContent->password);

        return $credentials;
    }

    /**
     * @inheritDoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $userProvider->loadUserByIdentifier($credentials->getUsername());

        if (!isset($user)) {
            throw new AuthenticationException('The user could not be found.');
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->userPasswordHasher->isPasswordValid($user, $credentials->getPassword());
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'error' => $exception->getMessageKey()
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new JsonResponse(['success' => true], JsonResponse::HTTP_OK);
    }

    /**
     * @inheritDoc
     */
    public function supportsRememberMe()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        $payload = isset($authException) ? array('error' => $authException->getMessageKey()) : null;
        return new JsonResponse($payload, JsonResponse::HTTP_UNAUTHORIZED);
    }
}
