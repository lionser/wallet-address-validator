<?php

declare(strict_types=1);

namespace Lionser\Tests;

use Lionser\Base58;
use Lionser\Base58Check;
use Lionser\WalletValidator;
use PHPUnit\Framework\TestCase;

final class WalletValidatorTest extends TestCase
{
    public function testKnownBtcGenesisAddressIsValid(): void
    {
        $this->assertTrue(WalletValidator::isValidBtcAddress('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2'));
        $this->assertFalse(WalletValidator::isValidBtcAddress('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN3'));
    }

    public function testBtcBech32AndTaprootAreValid(): void
    {
        $this->assertTrue(WalletValidator::isValidBtcAddress('BC1QW508D6QEJXTDG4Y5R3ZARVARY0C5XW7KV8F3T4'));
        $this->assertTrue(WalletValidator::isValidBtcAddress(
            'bc1p0xlxvlhemja6c4dqv22uapctqupfhlxm9h8z3k2e72q4k9hcz7vqzk5jj0'
        ));
        $this->assertFalse(WalletValidator::isValidBtcAddress(
            'bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kemeawh'
        ));
    }

    public function testSyntheticTrc20AddressIsValid(): void
    {
        $payload = chr(0x41) . random_bytes(20);
        $address = Base58Check::encode($payload);

        $this->assertTrue(WalletValidator::isValidTrc20Address($address));

        $lastChar = substr($address, -1);
        $tampered = substr($address, 0, -1) . ($lastChar === '1' ? '2' : '1');
        $this->assertFalse(WalletValidator::isValidTrc20Address($tampered));

        $this->assertFalse(WalletValidator::isValidTrc20Address('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2'));
    }

    public function testSyntheticSolanaAddressIsValid(): void
    {
        $address = Base58::encode(random_bytes(32));
        $this->assertTrue(WalletValidator::isValidSolanaAddress($address));

        $tooShort = Base58::encode(random_bytes(16));
        $this->assertFalse(WalletValidator::isValidSolanaAddress($tooShort));

        $this->assertFalse(WalletValidator::isValidSolanaAddress('not_base58_!!!_not_base58_!!!'));
    }

    public function testErc20AddressIsValid(): void
    {
        $this->assertTrue(WalletValidator::isValidErc20Address('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed'));
        $this->assertFalse(WalletValidator::isValidErc20Address('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAEd'));
    }

    public function testDetectNetworkCoversAllSupportedNetworks(): void
    {
        $trc20Address = Base58Check::encode(chr(0x41) . random_bytes(20));
        $solAddress   = Base58::encode(random_bytes(32));

        $this->assertSame(WalletValidator::NETWORK_TRC20, WalletValidator::detectNetwork($trc20Address));
        $this->assertSame(
            WalletValidator::NETWORK_BTC,
            WalletValidator::detectNetwork('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2')
        );
        $this->assertSame(
            WalletValidator::NETWORK_BTC,
            WalletValidator::detectNetwork('BC1QW508D6QEJXTDG4Y5R3ZARVARY0C5XW7KV8F3T4')
        );
        $this->assertSame(WalletValidator::NETWORK_SOL, WalletValidator::detectNetwork($solAddress));
        $this->assertSame(
            WalletValidator::NETWORK_ERC20,
            WalletValidator::detectNetwork('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed')
        );
        $this->assertNull(WalletValidator::detectNetwork('!!!invalid!!!'));
    }

    public function testIsValidMatchesDetectNetwork(): void
    {
        $this->assertTrue(WalletValidator::isValid('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed'));
        $this->assertFalse(WalletValidator::isValid('garbage'));
    }

    public function testValidateReturnsShapeWithNetwork(): void
    {
        $this->assertSame(
            ['valid' => true, 'network' => 'ERC20'],
            WalletValidator::validate('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed')
        );
        $this->assertSame(
            ['valid' => false, 'network' => null],
            WalletValidator::validate('garbage')
        );
    }
}
