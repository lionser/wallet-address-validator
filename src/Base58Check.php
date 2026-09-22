<?php

declare(strict_types=1);

namespace Lionser;

final class Base58Check
{
    private const CHECKSUM_LENGTH = 4;

    public static function decode(string $s): ?string
    {
        $bin = Base58::decode($s);

        if ($bin === null || strlen($bin) <= self::CHECKSUM_LENGTH) {
            return null;
        }

        $payload  = substr($bin, 0, -self::CHECKSUM_LENGTH);
        $checksum = substr($bin, -self::CHECKSUM_LENGTH);

        $expected = self::checksum($payload);

        if (!hash_equals($expected, $checksum)) {
            return null;
        }

        return $payload;
    }

    public static function encode(string $payload): string
    {
        return Base58::encode($payload . self::checksum($payload));
    }

    private static function checksum(string $payload): string
    {
        return substr(hash('sha256', hash('sha256', $payload, true), true), 0, self::CHECKSUM_LENGTH);
    }
}
