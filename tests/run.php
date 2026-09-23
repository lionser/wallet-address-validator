<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lionser\Base58;
use Lionser\Base58Check;
use Lionser\WalletValidator;

$failures = 0;
$passed   = 0;

function check(string $label, bool $condition): void
{
    global $failures, $passed;

    if ($condition) {
        $passed++;
        echo "  OK   $label\n";
    } else {
        $failures++;
        echo "  FAIL $label\n";
    }
}

echo "== Base58 roundtrip ==\n";

foreach ([1, 5, 20, 64] as $len) {
    $bin = random_bytes($len);
    $decoded = Base58::decode(Base58::encode($bin));
    check("roundtrip len=$len", $decoded === $bin);
}

check('empty string is invalid', Base58::decode('') === null);
check('invalid char (0) rejected', Base58::decode('10Ol') === null);

echo "\n== Base58Check ==\n";

$payload = "\x00" . random_bytes(20);
$encoded = Base58Check::encode($payload);
check('valid checksum decodes', Base58Check::decode($encoded) === $payload);

$tampered = substr($encoded, 0, -1) . (substr($encoded, -1) === '1' ? '2' : '1');
check('tampered address rejected', Base58Check::decode($tampered) === null);

echo "\n== BTC (legacy + bech32) ==\n";
check('known genesis address valid', WalletValidator::isValidBtcAddress('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2'));
check('tampered genesis address invalid', !WalletValidator::isValidBtcAddress('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN3'));
check('valid P2WPKH accepted', WalletValidator::isValidBtcAddress('BC1QW508D6QEJXTDG4Y5R3ZARVARY0C5XW7KV8F3T4'));
check('bech32/bech32m mismatch rejected', !WalletValidator::isValidBtcAddress('bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kemeawh'));

echo "\n== TRC20 ==\n";

$trc20Payload = chr(0x41) . random_bytes(20);
$trc20Address = Base58Check::encode($trc20Payload);
check('synthetic valid TRC20 accepted', WalletValidator::isValidTrc20Address($trc20Address));

$trc20Tampered = substr($trc20Address, 0, -1) . (substr($trc20Address, -1) === '1' ? '2' : '1');
check('tampered TRC20 rejected', !WalletValidator::isValidTrc20Address($trc20Tampered));

echo "\n== Solana ==\n";

$solAddress = Base58::encode(random_bytes(32));
check('synthetic 32-byte pubkey accepted', WalletValidator::isValidSolanaAddress($solAddress));
check('too short base58 string rejected', !WalletValidator::isValidSolanaAddress(Base58::encode(random_bytes(16))));

echo "\n== ERC20 / Ethereum (EIP-55, via kornrunner/keccak) ==\n";
check('EIP-55 vector accepted', WalletValidator::isValidErc20Address('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed'));
check('wrong checksum rejected', !WalletValidator::isValidErc20Address('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAEd'));

echo "\n== Universal detectNetwork() / validate() ==\n";
check('detects TRC20', WalletValidator::detectNetwork($trc20Address) === WalletValidator::NETWORK_TRC20);
check('detects BTC', WalletValidator::detectNetwork('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2') === WalletValidator::NETWORK_BTC);
check('detects SOL', WalletValidator::detectNetwork($solAddress) === WalletValidator::NETWORK_SOL);
check(
    'detects ERC20',
    WalletValidator::detectNetwork('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed') === WalletValidator::NETWORK_ERC20
);
check('garbage detects as null', WalletValidator::detectNetwork('!!!invalid!!!') === null);

echo "\n---\nPassed: $passed, Failed: $failures\n";
exit($failures > 0 ? 1 : 0);
