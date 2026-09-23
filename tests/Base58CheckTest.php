<?php

declare(strict_types=1);

namespace Lionser\Tests;

use Lionser\Base58Check;
use PHPUnit\Framework\TestCase;

final class Base58CheckTest extends TestCase
{
    public function testValidChecksumDecodes(): void
    {
        $payload = "\x00" . random_bytes(20);
        $encoded = Base58Check::encode($payload);

        $this->assertSame($payload, Base58Check::decode($encoded));
    }

    public function testTamperedAddressIsRejected(): void
    {
        $payload = "\x00" . random_bytes(20);
        $encoded = Base58Check::encode($payload);
        $lastChar = substr($encoded, -1);
        $tampered = substr($encoded, 0, -1) . ($lastChar === '1' ? '2' : '1');

        $this->assertNull(Base58Check::decode($tampered));
    }

    public function testTooShortStringIsRejected(): void
    {
        $this->assertNull(Base58Check::decode('abc'));
    }
}
