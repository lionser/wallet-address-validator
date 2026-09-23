<?php

declare(strict_types=1);

namespace Lionser\Tests;

use Lionser\EthAddress;
use PHPUnit\Framework\TestCase;

final class EthAddressTest extends TestCase
{
    /**
     * Официальные тест-векторы из спецификации EIP-55.
     *
     * @dataProvider eip55VectorProvider
     */
    public function testEip55ChecksumVectorsAreAccepted(string $address): void
    {
        $this->assertTrue(EthAddress::isValid($address));
    }

    public static function eip55VectorProvider(): array
    {
        return [
            ['0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed'],
            ['0xfB6916095ca1df60bB79Ce92cE3Ea74c37c5d359'],
            ['0xdbF03B407c01E7cD3CBea99509d93f8DDDC8C6FB'],
            ['0xD1220A0cf47c7B9Be7A2E6BA89F429762e7b9aDb'],
        ];
    }

    public function testAllLowercaseAcceptedWithoutChecksum(): void
    {
        $this->assertTrue(EthAddress::isValid('0x5aaeb6053f3e94c9b9a09f33669435e7ef1beaed'));
    }

    public function testAllUppercaseHexAcceptedWithoutChecksum(): void
    {
        $this->assertTrue(EthAddress::isValid('0x5AAEB6053F3E94C9B9A09F33669435E7EF1BEAED'));
    }

    public function testWrongChecksumCaseIsRejected(): void
    {
        $this->assertFalse(EthAddress::isValid('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAEd'));
    }

    public function testWrongLengthIsRejected(): void
    {
        $this->assertFalse(EthAddress::isValid('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeA'));
    }

    public function testMissingPrefixIsRejected(): void
    {
        $this->assertFalse(EthAddress::isValid('5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed'));
    }
}
