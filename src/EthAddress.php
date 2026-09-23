<?php

declare(strict_types=1);

namespace Lionser;

use kornrunner\Keccak;

/**
 * Ethereum / ERC20-адреса. Логика повторяет ethers.isAddress(): смешанный
 * регистр обязан совпасть с EIP-55 чек-суммой (Keccak-256), не смешанный —
 * принимается без проверки чек-суммы.
 */
final class EthAddress
{
    public static function isValid(string $address): bool
    {
        if (!preg_match('/^0x[0-9a-fA-F]{40}$/', $address)) {
            return false;
        }

        $hex = substr($address, 2);

        $isAllLower = $hex === strtolower($hex);
        $isAllUpper = $hex === strtoupper($hex);

        if ($isAllLower || $isAllUpper) {
            return true;
        }

        return self::toChecksumAddress($hex) === $hex;
    }

    public static function toChecksumAddress(string $hexNoPrefix): string
    {
        $lower = strtolower($hexNoPrefix);
        $hash  = Keccak::hash($lower, 256);

        $result = '';

        for ($i = 0, $len = strlen($lower); $i < $len; $i++) {
            $char = $lower[$i];

            if ($char < '0' || $char > '9') {
                $result .= hexdec($hash[$i]) >= 8 ? strtoupper($char) : $char;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }
}
