<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\Constant\ExceptionCodes;
use App\Service\GlobalId\GlobalIdProvider;
use App\Service\GraphQL\FieldEncryptionProvider;
use App\Service\Security\XssFilter;
use \Exception as Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function is_string;
use function is_array;

/**
 * MutationInputReShaper provides the methods to re-shape a mutation input array
 * by correcting the mismatches between the graphql api fields and the documents fields
 * (E.g. decode a globalId and store the id in the input array, or decrypt the encryptedVersion)
 */
final class MutationInputReShaper
{

    public function __construct(
        private GlobalIdProvider $globalIdProvider,
        private FieldEncryptionProvider $fieldEncryptionProvider
    ) {
    }


    /**
     * Apply all necessary changes to array 
     * to make it suitable for hydration and mapping.
     */
    public function reShape(array $data): array
    {
        $data = $this->decodeIds($data);
        $data = $this->decryptVersions($data);

        return $data;
    }

    /**
     * Decode all ids and replace their values with their decoded values.
     * In the domain model and API layer the entities are identified by opaque ids,
     * but the persistence layer uses the normal ids. To hydrating objects with the provided data
     * we have to restore the ids.
     */
    private function decodeIds(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->decodeIds($value);
            } elseif (($key === 'id' || strpos((string)$key, 'Id') > 0) && $this->globalIdProvider->isValidGlobalId($value)) {
                //There are some situations in which decoding a global id can fail:
                //In all situations it is enough to catch the exception without further processing.  
                try {
                    $decodedGlobalId = $this->globalIdProvider->getIdFromGlobalId((string)$value);
                    $data[$key] = $decodedGlobalId;
                } catch (Exception) {
                }
            }
        }

        return $data;
    }

    /**
     * Decrypt all encrypted versions and replace the values with their decrypted values.
     * In the API layer the document's versions are encrypted to avoid tampering.
     * To hydrate objects with the given data we have to restore the original version value.
     *
     * IMPORTANT: make sure the $data array contains decoded ids before running this method on an array:
     * the decoded ids are necessary to decrypt the version (Use MutationInputReShaper::decodeIds() to decode the ids).
     */
    private function decryptVersions(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->decryptVersions($value);
            } elseif ($key === 'version') {
                try {
                    $data['version'] = (int) $this->fieldEncryptionProvider->decrypt($value, $data['id']);
                } catch (Exception $exception) {
                    throw new BadRequestHttpException(
                        'The value of \'version\' is not valid and cannot be decrypted. (Maybe you forgot to decode the ids before trying to decrypt the versions?)',
                        $exception,
                        ExceptionCodes::TAMPERING_EXCEPTION,
                    );
                }
            }
        }
        return $data;
    }
}
