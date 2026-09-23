<?php

declare(strict_types=1);

namespace Lionser\Tests;

use Lionser\Base58;
use PHPUnit\Framework\TestCase;

final class Base58Test extends TestCase
{
    /**
     * @dataProvider roundtripLengthProvider
     */
    public function testRoundtrip(int $length): void
    {
        $bin = random_bytes($length);

        $this->assertSame($bin, Base58::decode(Base58::encode($bin)));
    }

    public static function roundtripLengthProvider(): array
    {
        return [[1], [5], [20], [64]];
    }

    public function testEmptyStringIsInvalid(): void
    {
        $this->assertNull(Base58::decode(''));
    }

    public function testInvalidAlphabetCharsAreRejected(): void
    {
        $this->assertNull(Base58::decode('10Ol'));
        $this->assertFalse(Base58::isValidAlphabet('0'));
        $this->assertFalse(Base58::isValidAlphabet('O'));
        $this->assertFalse(Base58::isValidAlphabet('I'));
        $this->assertFalse(Base58::isValidAlphabet('l'));
    }

    public function testLeadingZeroBytesBecomeLeadingOnes(): void
    {
        $bin = "\x00\x00" . random_bytes(10);
        $encoded = Base58::encode($bin);

        $this->assertStringStartsWith('11', $encoded);
        $this->assertSame($bin, Base58::decode($encoded));
    }
}
