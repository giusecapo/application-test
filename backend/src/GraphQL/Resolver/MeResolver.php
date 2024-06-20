<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\DocumentModel\UserModel;
use App\Security\Constant\Roles;
use App\Service\Document\SecurityProvider;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use App\Utility\MethodsBuilder;
use App\Service\GraphQL\Me;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use function in_array;

final class MeResolver implements ResolverInterface
{

    /**
     * Here we store all fields which need a 'complex' field resolver with custom logic.
     * All other fields will be resolved by calling the getter method on the object
     */
    private const COMPLEX_RESOLVER_FIELDS = [
        'grantedRoles',
        'isGrantedRole',
    ];

    public function __construct(
        private Security $security,
        private RoleHierarchyInterface $roleHierarchy,
        private SecurityProvider $securityProvider,
        private UserModel $userModel
    ) {
    }


    public function resolveMe(): Me
    {
        $user = $this->security->getUser() !== null
            ? $this->userModel->getRepository()->getByUsername($this->security->getUser()->getUserIdentifier(), false)
            : null;

        $me = new Me();
        $me
            ->setUser($user)
            ->setAuthenticated($this->security->getUser() !== null);

        return $me;
    }

    /**
     * This magic method is called to resolve each simple field of the Me type. 
     * @param  Me $me
     */
    public function __invoke(ResolveInfo $info, $me, ArgumentInterface $args): mixed
    {
        if (!in_array($info->fieldName, static::COMPLEX_RESOLVER_FIELDS)) {
            $getterMethodForField = MethodsBuilder::toGetMethod($info->fieldName);
            return $me->$getterMethodForField();
        }
        $getterMethodForField = MethodsBuilder::toResolveMethod($info->fieldName);
        return $this->$getterMethodForField($me, $args);
    }

    public function resolveGrantedRoles(Me $me): array
    {
        return $this->roleHierarchy->getReachableRoleNames($me->getUser()?->getRoles() ?? array());
    }

    public function resolveIsGrantedRole(Me $me, ArgumentInterface $args): bool
    {
        $reachableRoles = $me->getUser() !== null
            ? $this->roleHierarchy->getReachableRoleNames($me->getUser()->getRoles())
            : array(Roles::IS_ANONYMOUS);

        if (in_array($args['role'], $reachableRoles)) {
            return true;
        }
        return false;
    }
}
