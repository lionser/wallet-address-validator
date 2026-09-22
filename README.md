# wallet-address-validator

Лёгкий PHP-валидатор адресов криптокошельков — **BTC** (legacy + bech32/taproot),
**TRC20 (Tron)** и **Solana**.

Без внешних зависимостей (не требует GMP/BCMath) — Base58 и Bech32/Bech32m
реализованы вручную и сверены с официальными тест-векторами (BIP-173/BIP-350).

## Что именно проверяется

| Сеть  | Формат                          | Проверка                                                              |
|-------|----------------------------------|-------------------------------------------------------------------------|
| BTC   | legacy P2PKH/P2SH                 | Base58Check: чек-сумма (двойной SHA256) + version-байт `0x00`/`0x05`     |
| BTC   | native segwit (`bc1...`), taproot | Bech32 (witver 0) / Bech32m (witver 1-16), проверка длины и версии — BIP-141/173/350 |
| TRC20 | Tron mainnet (`T...`)             | Base58Check: чек-сумма + version-байт `0x41`                            |
| SOL   | ed25519 публичный ключ            | Base58 (без чек-суммы!), декодированная длина строго 32 байта           |

## Установка

```bash
composer require lionser/wallet-address-validator
```

Либо просто скопируйте `src/*.php` в проект — зависимостей нет.

## Использование

### Универсальный метод (сам определяет сеть)

```php
use WalletAddressValidator\WalletValidator;

WalletValidator::isValid($anyWallet); // bool — валиден хотя бы для одной сети

WalletValidator::detectNetwork($anyWallet);
// 'BTC' | 'TRC20' | 'SOL' | null
```

### Проверка конкретной сети

```php
WalletValidator::isValidBtcAddress('1BvBMSEYstWetqTFn5Au4m4GFg7xJaNVN2');   // true (legacy)
WalletValidator::isValidBtcAddress('bc1qw508d6qejxtdg4y5r3zarvary0c5xw7kv8f3t4'); // true (bech32)
WalletValidator::isValidTrc20Address('TXYZ...');
WalletValidator::isValidSolanaAddress('DYw8jCTfwHNRJhhmFcbXvVDTqWMEVFBX6ZKUmG5CNSKK');
```

## Тесты

Без PHPUnit — простой assert-based раннер, проверяет всё на официальных
тест-векторах (BIP-173/350 для bech32, а также известный BTC genesis-адрес):

```bash
php tests/run.php
```

## Что НЕ входит в этот пакет

- ERC20/Ethereum-адреса — сознательно не поддерживаются (не нужны для текущего проекта).
- Валидация "Capitalist"-подобных внутренних идентификаторов платёжных систем
  (`/^[A-Z]\d+$/i`) — это не блокчейн-адрес, добавьте отдельной функцией при необходимости.
- Проверка testnet-адресов (только mainnet-префиксы/version-байты).

## Лицензия

MIT
