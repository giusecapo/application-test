<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Document\User;
use App\DocumentModel\UserModel;
use App\Service\GraphQL\Buffer;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use \ArrayObject;
use App\Service\GlobalId\GlobalIdProvider;
use App\Utility\MethodsBuilder;
use GraphQL\Deferred;
use App\Service\GraphQL\FieldEncryptionProvider;
use Doctrine\Common\Collections\Collection;
use App\Service\GraphQL\GetByFieldValuesQueryArgumentsProvider;
use function in_array;

final class UserResolver implements ResolverInterface
{
    /**
     * Here we store all fields which need a 'complex' field resolver with custom logic.
     * All other fields will be resolved by calling the getter method on the object
     */
    private const COMPLEX_RESOLVER_FIELDS = [
        'id',
        'version',
    ];

    public function __construct(
        private UserModel $userModel,
        private GlobalIdProvider $globalIdProvider,
        private Buffer $buffer,
        private FieldEncryptionProvider $fieldEncryptionProvider,
        private GetByFieldValuesQueryArgumentsProvider $getByFieldValuesQueryArgumentsProvider
    ) {
    }

    public function resolveOneByUsername(string $username): Deferred
    {
        $buffer = $this->buffer;
        $model = $this->userModel;
        $getByFieldValuesQueryArgumentsProvider = $this->getByFieldValuesQueryArgumentsProvider;
        $buffer->add(User::class, 'username', $username);

        return new Deferred(fn () => $buffer->get(
            User::class,
            'username',
            $username,
            function ($usernames) use ($model, $getByFieldValuesQueryArgumentsProvider): Collection {
                $queryCriteria = $getByFieldValuesQueryArgumentsProvider->toQueryCriteria('username', $usernames);
                return $model->getRepository()->find($queryCriteria);
            }
        ));
    }

    /**
     * This magic method is called to resolve each field of the User type. 
     * @param  User $user
     */
    public function __invoke(ResolveInfo $info, $user, ArgumentInterface $args, ArrayObject $context): mixed
    {
        if (!in_array($info->fieldName, static::COMPLEX_RESOLVER_FIELDS)) {
            $getterMethodForField = MethodsBuilder::toGetMethod($info->fieldName);
            return $user->$getterMethodForField();
        }
        $getterMethodForField = MethodsBuilder::toResolveMethod($info->fieldName);
        return $this->$getterMethodForField($user, $args);
    }

    private function resolveId(User $user): string
    {
        return $this->globalIdProvider->toGlobalId($user);
    }

    private function resolveVersion(User $user): string
    {
        $version = (string) $user->getVersion();
        $id = $user->getId();
        return $this->fieldEncryptionProvider->encrypt($version, $id);
    }
}
