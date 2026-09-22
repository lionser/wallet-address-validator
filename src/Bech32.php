<?php

declare(strict_types=1);

namespace Lionser;

final class Bech32
{
    private const CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

    private const BECH32M_CONST = 0x2bc830a3;

    private const ENCODING_BECH32  = 'bech32';
    private const ENCODING_BECH32M = 'bech32m';

    public static function decodeSegwitAddress(string $expectedHrp, string $address): ?array
    {
        [$hrp, $data, $spec] = self::bech32Decode($address);

        if ($hrp === null || $hrp !== $expectedHrp) {
            return null;
        }

        $decoded = self::convertBits(array_slice($data, 1), 5, 8, false);

        if ($decoded === null || count($decoded) < 2 || count($decoded) > 40) {
            return null;
        }

        $witnessVersion = $data[0];

        if ($witnessVersion > 16) {
            return null;
        }

        if ($witnessVersion === 0 && count($decoded) !== 20 && count($decoded) !== 32) {
            return null;
        }

        $expectedSpec = $witnessVersion === 0 ? self::ENCODING_BECH32 : self::ENCODING_BECH32M;

        if ($spec !== $expectedSpec) {
            return null;
        }

        return [$witnessVersion, $decoded];
    }

    private static function bech32Decode(string $bech): array
    {
        $len = strlen($bech);

        for ($i = 0; $i < $len; $i++) {
            $ord = ord($bech[$i]);

            if ($ord < 33 || $ord > 126) {
                return [null, null, null];
            }
        }

        $lower = strtolower($bech);
        $upper = strtoupper($bech);

        if ($bech !== $lower && $bech !== $upper) {
            return [null, null, null];
        }

        $bech = $lower;

        $pos = strrpos($bech, '1');

        if ($pos === false || $pos < 1 || $pos + 7 > strlen($bech)) {
            return [null, null, null];
        }

        $hrp      = substr($bech, 0, $pos);
        $dataPart = substr($bech, $pos + 1);

        $data = [];

        for ($i = 0, $l = strlen($dataPart); $i < $l; $i++) {
            $idx = strpos(self::CHARSET, $dataPart[$i]);

            if ($idx === false) {
                return [null, null, null];
            }

            $data[] = $idx;
        }

        $spec = self::verifyChecksum($hrp, $data);

        if ($spec === null) {
            return [null, null, null];
        }

        return [$hrp, array_slice($data, 0, -6), $spec];
    }

    private static function verifyChecksum(string $hrp, array $data): ?string
    {
        $check = self::polymod(array_merge(self::hrpExpand($hrp), $data));

        if ($check === 1) {
            return self::ENCODING_BECH32;
        }

        if ($check === self::BECH32M_CONST) {
            return self::ENCODING_BECH32M;
        }

        return null;
    }

    private static function polymod(array $values): int
    {
        $generator = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];

        $chk = 1;

        foreach ($values as $value) {
            $top = $chk >> 25;
            $chk = (($chk & 0x1ffffff) << 5) ^ $value;

            for ($i = 0; $i < 5; $i++) {
                if ((($top >> $i) & 1) !== 0) {
                    $chk ^= $generator[$i];
                }
            }
        }

        return $chk;
    }

    private static function hrpExpand(string $hrp): array
    {
        $result = [];

        for ($i = 0, $len = strlen($hrp); $i < $len; $i++) {
            $result[] = ord($hrp[$i]) >> 5;
        }

        $result[] = 0;

        for ($i = 0, $len = strlen($hrp); $i < $len; $i++) {
            $result[] = ord($hrp[$i]) & 31;
        }

        return $result;
    }

    private static function convertBits(array $data, int $fromBits, int $toBits, bool $pad): ?array
    {
        $acc = 0;
        $bits = 0;
        $result = [];
        $maxv = (1 << $toBits) - 1;
        $maxAcc = (1 << ($fromBits + $toBits - 1)) - 1;

        foreach ($data as $value) {
            if ($value < 0 || ($value >> $fromBits) !== 0) {
                return null;
            }

            $acc = (($acc << $fromBits) | $value) & $maxAcc;

            $bits += $fromBits;

            while ($bits >= $toBits) {
                $bits -= $toBits;
                $result[] = ($acc >> $bits) & $maxv;
            }
        }

        if ($pad) {
            if ($bits > 0) {
                $result[] = ($acc << ($toBits - $bits)) & $maxv;
            }
        } elseif ($bits >= $fromBits || ((($acc << ($toBits - $bits)) & $maxv) !== 0)) {
            return null;
        }

        return $result;
    }
}
