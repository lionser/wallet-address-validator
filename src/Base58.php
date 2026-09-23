<?php

declare(strict_types=1);

namespace Lionser;

final class Base58
{
    public const ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    public static function isValidAlphabet(string $s): bool
    {
        if ($s === '') {
            return false;
        }

        return (bool) preg_match('/^[' . preg_quote(self::ALPHABET, '/') . ']+$/', $s);
    }

    public static function decode(string $s): ?string
    {
        if (!self::isValidAlphabet($s)) {
            return null;
        }

        $alphabetMap = array_flip(str_split(self::ALPHABET));

        $leadingZeros = 0;

        while (isset($s[$leadingZeros]) && $s[$leadingZeros] === '1') {
            $leadingZeros++;
        }

        $digits = [0];

        for ($i = 0, $len = strlen($s); $i < $len; $i++) {
            $value = $alphabetMap[$s[$i]] ?? null;

            if ($value === null) {
                return null;
            }

            $carry = $value;

            for ($j = 0, $digitsCount = count($digits); $j < $digitsCount; $j++) {
                $carry += $digits[$j] * 58;
                $digits[$j] = $carry & 0xff;
                $carry >>= 8;
            }

            while ($carry > 0) {
                $digits[] = $carry & 0xff;
                $carry >>= 8;
            }
        }

        $bytes = array_reverse($digits);
        $bytes = ltrim(implode('', array_map('chr', $bytes)), "\x00");

        return str_repeat("\x00", $leadingZeros) . $bytes;
    }

    public static function encode(string $binary): string
    {
        $leadingZeros = 0;

        while (isset($binary[$leadingZeros]) && $binary[$leadingZeros] === "\x00") {
            $leadingZeros++;
        }

        $bytes  = $binary === '' ? [] : array_values(unpack('C*', $binary));
        $digits = [0];

        foreach ($bytes as $byte) {
            $carry = $byte;

            for ($j = 0, $digitsCount = count($digits); $j < $digitsCount; $j++) {
                $carry += $digits[$j] << 8;
                $digits[$j] = $carry % 58;
                $carry = intdiv($carry, 58);
            }

            while ($carry > 0) {
                $digits[] = $carry % 58;
                $carry = intdiv($carry, 58);
            }
        }

        $result = str_repeat('1', $leadingZeros);

        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $result .= self::ALPHABET[$digits[$i]];
        }

        return $result;
    }
}
