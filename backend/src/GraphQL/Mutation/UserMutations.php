<?php

namespace App\GraphQL\Mutation;

use App\DocumentModel\UserModel;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use App\Document\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Service\GraphQL\MutationInputReShaper;
use App\Service\Constant\ExceptionCodes;
use App\Service\GraphQL\ValidationHelper;

final class UserMutations implements MutationInterface
{

    public function __construct(
        private UserModel $userModel,
        private MutationInputReShaper $mutationInputReShaper,
        private ValidationHelper $validationHelper
    ) {
    }

    public function createUser(array $input): array
    {
        $input = $this->mutationInputReShaper->reShape($input);
        $user = new User();
        $this->userModel->getMapper()->map($user, $input);
        $this->validationHelper->validateType($user, ['user_create']);
        $this->userModel->create($user);
        $this->userModel->getWriteManager()->flush();

        return [
            'user' => $user
        ];
    }

    public function updateUser(array $input): array
    {
        $input = $this->mutationInputReShaper->reShape($input);
        $user = $this->userModel->getRepository()->getById($input['id'], false);
        if (!isset($user)) {
            throw new BadRequestHttpException(
                sprintf('User with id %s does not exist', $input['id']),
                null,
                ExceptionCodes::BAD_REQUEST_EXCEPTION
            );
        }
        $this->userModel->getMapper()->map($user, $input);
        $this->validationHelper->validateType($user, ['user_update']);
        $this->userModel->update($user, null, $input['version']);
        $this->userModel->getWriteManager()->flush();

        return [
            'user' => $user
        ];
    }
}
