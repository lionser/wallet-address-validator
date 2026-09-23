# wallet-address-validator

PHP-валидатор адресов криптокошельков — **BTC** (legacy + bech32/taproot),
**TRC20 (Tron)**, **Solana** и **ERC20/Ethereum** (в т.ч. USDC-ERC20, USDT-ERC20 —
формат адреса у токенов ERC20 такой же, как у обычного ETH-адреса).

Base58, Base58Check и Bech32/Bech32m реализованы вручную (без GMP/BCMath) и
сверены с официальными тест-векторами (BIP-173/BIP-350). Для Keccak-256
(EIP-55 чек-сумма ETH-адресов) используется [`kornrunner/keccak`](https://github.com/kornrunner/php-keccak).

## Требования

- PHP **>= 8.3**

## Что именно проверяется

| Сеть  | Формат                          | Проверка                                                              |
|-------|----------------------------------|-------------------------------------------------------------------------|
| BTC   | legacy P2PKH/P2SH                 | Base58Check: чек-сумма (двойной SHA256) + version-байт `0x00`/`0x05`     |
| BTC   | native segwit (`bc1...`), taproot | Bech32 (witver 0) / Bech32m (witver 1-16), проверка длины и версии — BIP-141/173/350 |
| TRC20 | Tron mainnet (`T...`)             | Base58Check: чек-сумма + version-байт `0x41`                            |
| SOL   | ed25519 публичный ключ            | Base58 (без чек-суммы!), декодированная длина строго 32 байта           |
| ERC20 | Ethereum-адрес (`0x...`)          | Формат hex + EIP-55 чек-сумма (Keccak-256 через `kornrunner/keccak`)     |

## Установка

```bash
composer require lionser/wallet-address-validator
```

## Использование

### Универсальный метод (сам определяет сеть)

```php
use Lionser\WalletValidator;

WalletValidator::isValid($anyWallet); // bool — валиден хотя бы для одной сети

WalletValidator::detectNetwork($anyWallet);
// 'BTC' | 'TRC20' | 'SOL' | 'ERC20' | null

WalletValidator::validate($anyWallet);
// ['valid' => true, 'network' => 'ERC20']
```

### Проверка конкретной сети

```php
WalletValidator::isValidBtcAddress('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2');   // true (legacy)
WalletValidator::isValidBtcAddress('bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kv8f3t4'); // true (bech32)
WalletValidator::isValidTrc20Address('TXYZ...');
WalletValidator::isValidSolanaAddress('DYw8jCTfwHNRJhhmFcbXvVDTqWMEVFBX6ZKUmG5CNSKK');
WalletValidator::isValidErc20Address('0x5aAeb6053F3E94C9b9A09f33669435E7Ef1BeAed'); // с EIP-55 чек-суммой
```

## Разработка / тесты / качество кода

```bash
composer install

composer test    # PHPUnit — все официальные тест-векторы BIP-350/EIP-55
composer cs       # PHP_CodeSniffer, PSR-12
composer cs-fix   # автофикс
composer stan     # PHPStan, level 8
composer check    # cs + stan + test — то же самое гоняет CI
```

Без dev-зависимостей — `php tests/run.php` (нужен только `composer install`).

## Что НЕ входит в этот пакет

- Валидация "Capitalist"-подобных внутренних идентификаторов платёжных систем
  (`/^[A-Z]\d+$/i`) — это не блокчейн-адрес, добавьте отдельной функцией при необходимости.
- Проверка testnet-адресов (только mainnet-префиксы/version-байты).

## Лицензия

MIT
