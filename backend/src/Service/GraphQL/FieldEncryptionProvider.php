<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\Security\EncryptionProvider;
use App\Utility\CappedSet;

final class FieldEncryptionProvider
{

    private CappedSet $memoizedEncryptedFields;

    private CappedSet $memoizedDecryptedFields;


    public function __construct(
        private EncryptionProvider $encryptionProvider,
        private string $appSecret
    ) {
        $this->memoizedEncryptedFields = new CappedSet(100000);
        $this->memoizedDecryptedFields = new CappedSet(100000);
    }


    public function encrypt(string $value, string $id): string
    {
        $memoizedEncryptedFieldKey = md5($id . '|' . $value);
        if (!$this->memoizedEncryptedFields->isset($memoizedEncryptedFieldKey)) {
            $key = $this->prepareKey($id);
            $this->memoizedEncryptedFields->add($memoizedEncryptedFieldKey, $this->encryptionProvider->encrypt($value, $key));
        }

        return $this->memoizedEncryptedFields->get($memoizedEncryptedFieldKey);
    }

    public function decrypt(string $value, string $id): string
    {
        $memoizedDecryptedFieldKey = md5($id . '|' . $value);
        if (!$this->memoizedDecryptedFields->isset($memoizedDecryptedFieldKey)) {
            $key = $this->prepareKey($id);
            $this->memoizedDecryptedFields->add($memoizedDecryptedFieldKey, $this->encryptionProvider->decrypt($value, $key));
        }

        return $this->memoizedDecryptedFields->get($memoizedDecryptedFieldKey);
    }

    private function prepareKey(string $id): string
    {
        return sprintf('%s%s', $this->appSecret, $id);
    }
}
