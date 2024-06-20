<?php

declare(strict_types=1);

namespace App\Service\Security;

use \InvalidArgumentException as InvalidArgumentException;
use \RuntimeException as RuntimeException;
use App\Service\Constant\ExceptionCodes;
use function strlen;
use function in_array;

final class EncryptionProvider
{

    public function __construct(protected string $cipherMethod = 'aes-128-ctr')
    {
        //check if the supplied encryption method exists and is supported
        if (!in_array(strtolower($this->cipherMethod), openssl_get_cipher_methods())) {
            throw new InvalidArgumentException(
                sprintf('Unrecognized cipher method: %s', $this->cipherMethod),
                ExceptionCodes::INVALID_ARGUMENT_EXCEPTION
            );
        }
    }

    /**
     * @throws RuntimeException on failure
     */
    public function encrypt(string $value, string $key): string
    {
        $key = $this->prepareKey($key);
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $hexIv = bin2hex($iv);
        $encryptedValue = openssl_encrypt($value, $this->cipherMethod, $key, 0, $iv);
        if ($encryptedValue) {
            return $hexIv . $encryptedValue;
        }
        throw new RuntimeException('String encryption failed.', ExceptionCodes::RUNTIME_EXCEPTION);
    }

    /**
     * @throws RuntimeException on failure
     */
    public function decrypt(string $value, string $key): string
    {
        $key = $this->prepareKey($key);
        $ivLength = openssl_cipher_iv_length($this->cipherMethod) * 2; //*2 because the value was converted to hex
        if (preg_match("/^(.{" . $ivLength . "})(.+)$/", $value, $matches)) {
            $iv = $matches[1];
            $encryptedValue = $matches[2];
            if (ctype_xdigit($iv) && strlen($iv) % 2 == -0) {
                return openssl_decrypt($encryptedValue, $this->cipherMethod, $key, 0, hex2bin($iv));
            }
        }
        throw new RuntimeException('String decryption failed', ExceptionCodes::RUNTIME_EXCEPTION);
    }

    private function prepareKey(string $key): string
    {
        // convert ASCII keys to binary format if necessary
        return ctype_print($key)
            ? openssl_digest($key, 'SHA256', TRUE)
            : $key;
    }
}
