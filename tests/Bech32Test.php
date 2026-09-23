<?php

declare(strict_types=1);

namespace Lionser\Tests;

use Lionser\Bech32;
use PHPUnit\Framework\TestCase;

final class Bech32Test extends TestCase
{
    /**
     * Официальные валидные тест-векторы из BIP-350.
     *
     * @dataProvider validAddressProvider
     */
    public function testValidAddressesAreAccepted(string $hrp, string $address): void
    {
        $this->assertNotNull(Bech32::decodeSegwitAddress($hrp, $address));
    }

    public static function validAddressProvider(): array
    {
        return [
            ['bc', 'BC1QW508D6QEJXTDG4Y5R3ZARVARY0C5XW7KV8F3T4'],
            ['tb', 'tb1qrp33g0q5c5txsp9arysrx4k6zdkfs4nce4xj0gdcccefvpysxf3q0sl5k7'],
            ['bc', 'bc1pw508d6qejxtdg4y5r3zarvary0c5xw7kw508d6qejxtdg4y5r3zarvary0c5xw7kt5nd6y'],
            ['bc', 'BC1SW50QGDZ25J'],
            ['bc', 'bc1zw508d6qejxtdg4y5r3zarvaryvaxxpcs'],
            ['tb', 'tb1qqqqqp399et2xygdj5xreqhjjvcmzhxw4aywxecjdzew6hylgvsesrxh6hy'],
            ['tb', 'tb1pqqqqp399et2xygdj5xreqhjjvcmzhxw4aywxecjdzew6hylgvsesf3hn0c'],
            ['bc', 'bc1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vqzk5jj0'],
        ];
    }

    /**
     * Официальные невалидные тест-векторы из BIP-350 (в т.ч. критичный кейс
     * несоответствия версии witness-программы и кодировки Bech32/Bech32m).
     *
     * @dataProvider invalidAddressProvider
     */
    public function testInvalidAddressesAreRejected(string $hrp, string $address, string $reason): void
    {
        $this->assertNull(Bech32::decodeSegwitAddress($hrp, $address), $reason);
    }

    public static function invalidAddressProvider(): array
    {
        return [
            ['bc', 'tc1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vq5zuyut', 'Invalid HRP'],
            ['bc', 'bc1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vqh2y7hd', 'Bech32 instead of Bech32m'],
            ['tb', 'tb1z0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vqglt7rf', 'Bech32 instead of Bech32m'],
            ['bc', 'BC1S0XLXVLHEMJA6C4DQV22UAPCTQUPFHLXM9H8Z3K2E72Q4K9HCZ7VQ54WELL', 'Bech32 instead of Bech32m'],
            ['bc', 'bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kemeawh', 'Bech32m instead of Bech32'],
            ['tb', 'tb1q0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vq24jc47', 'Bech32m instead of Bech32'],
            ['bc', 'bc1p38j9r5y49hruaue7wxjce0updqjuyyx0kh56v8s25huc6995vvpql3jow4', 'Invalid char in checksum'],
            ['bc', 'BC130XLXVLHEMJA6C4DQV22UAPCTQUPFHLXM9H8Z3K2E72Q4K9HCZ7VQ7ZWS8R', 'Invalid witness version'],
            ['bc', 'bc1pw5dgrnzv', 'Invalid program length (1 byte)'],
            ['bc', 'bc1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7v8n0nx0muaewav253zgeav', 'Invalid program length (41 bytes)'],
            ['bc', 'BC1QR508D6QEJXTDG4Y5R3ZARVARYV98GJ9P', 'Invalid program length for v0'],
            ['tb', 'tb1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vq47Zagq', 'Mixed case'],
            ['bc', 'bc1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7v07qwwzcrf', 'Zero padding > 4 bits'],
            ['tb', 'tb1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vpggkg4j', 'Non-zero padding'],
            ['bc', 'bc1gmk9yu', 'Empty data section'],
        ];
    }
}
