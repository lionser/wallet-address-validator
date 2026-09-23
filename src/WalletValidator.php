<?php

declare(strict_types=1);

namespace Lionser;

final class WalletValidator
{
    public const NETWORK_BTC   = 'BTC';
    public const NETWORK_TRC20 = 'TRC20';
    public const NETWORK_SOL   = 'SOL';
    public const NETWORK_ERC20 = 'ERC20';

    /** Bitcoin mainnet: P2PKH и P2SH. */
    private const array BTC_VERSION_BYTES = [0x00, 0x05];

    /** Tron mainnet. */
    private const int TRC20_VERSION_BYTE = 0x41;

    private const int SOL_PUBKEY_LENGTH = 32;

    public static function isValidBtcAddress(string $address): bool
    {
        if (preg_match('/^bc1[a-zA-HJ-NP-Z0-9]{6,}$/i', $address)) {
            return Bech32::decodeSegwitAddress('bc', $address) !== null;
        }

        if (!preg_match('/^[13][1-9A-HJ-NP-Za-km-z]{25,34}$/', $address)) {
            return false;
        }

        $payload = Base58Check::decode($address);

        if ($payload === null || strlen($payload) !== 21) {
            return false;
        }

        return in_array(ord($payload[0]), self::BTC_VERSION_BYTES, true);
    }

    public static function isValidTrc20Address(string $address): bool
    {
        if (!preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $address)) {
            return false;
        }

        $payload = Base58Check::decode($address);

        if ($payload === null || strlen($payload) !== 21) {
            return false;
        }

        return ord($payload[0]) === self::TRC20_VERSION_BYTE;
    }

    public static function isValidSolanaAddress(string $address): bool
    {
        $length = strlen($address);

        if ($length < 32 || $length > 44) {
            return false;
        }

        $bin = Base58::decode($address);

        return $bin !== null && strlen($bin) === self::SOL_PUBKEY_LENGTH;
    }

    public static function isValidErc20Address(string $address): bool
    {
        return EthAddress::isValid($address);
    }

    public static function detectNetwork(string $address): ?string
    {
        $address = trim($address);

        if (self::isValidErc20Address($address)) {
            return self::NETWORK_ERC20;
        }

        if (self::isValidTrc20Address($address)) {
            return self::NETWORK_TRC20;
        }

        if (self::isValidBtcAddress($address)) {
            return self::NETWORK_BTC;
        }

        if (self::isValidSolanaAddress($address)) {
            return self::NETWORK_SOL;
        }

        return null;
    }

    public static function isValid(string $address): bool
    {
        return self::detectNetwork($address) !== null;
    }

    /**
     * @return array{valid: bool, network: string|null}
     */
    public static function validate(string $address): array
    {
        $network = self::detectNetwork($address);

        return [
            'valid'   => $network !== null,
            'network' => $network,
        ];
    }
}
