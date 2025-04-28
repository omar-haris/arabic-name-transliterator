# Card Name Fit

A simple PHP library that solves the headache of fitting names on cards and badges. If you've ever struggled with names that are too long for credit cards, ID badges, or passport printing, this is your solution.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/omar-haris/card-name-fit.svg)](https://packagist.org/packages/omar-haris/card-name-fit)
[![Tests](https://github.com/omar-haris/card-name-fit/actions/workflows/tests.yml/badge.svg)](https://github.com/omar-haris/card-name-fit/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/omar-haris/card-name-fit.svg)](https://packagist.org/packages/omar-haris/card-name-fit)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Total Downloads](https://img.shields.io/packagist/dt/omar-haris/card-name-fit.svg)](https://packagist.org/packages/omar-haris/card-name-fit)

## Why This Library?

We've all seen ID cards with awkwardly truncated names or ridiculously small fonts trying to fit long names. This library fixes that problem with intelligent name formatting for fixed-width spaces.

It's especially useful for:
- Card printing (credit, debit, ID, membership)
- Name badges and tags
- Passports and government IDs 
- Any system with character limits for names

## Key Features

- **Smart formatting** that preserves readability
- **Handles both English and Arabic** names with different strategies
- **Adjustable character limits** to match your exact requirements
- **UTF-8 support** for international names
- **No external dependencies** besides PHP's mbstring

## Installation

Just run:

```bash
composer require omar-haris/card-name-fit
```

## Basic Usage

```php
use CardNameFit\NameFormatter;

// Create a formatter with 20-character limit
$formatter = new NameFormatter(20);

// Format some names
echo $formatter->format('John William Smith');             // "John William Smith"
echo $formatter->format('John William Alexander Smith');   // "John William Smith"

// Works with Arabic names too
echo $formatter->format('محمد عبد الرحمن العبد الله');     // "محمد عبد الرحمن"
```

## Formatting Strategies

### For English Names

The library offers two approaches:

#### 1. GREEDY (default)

Keeps full middle names when possible, drops them when needed. People generally prefer seeing their complete names rather than initials.

```php
// With 20 character limit
$formatter = new NameFormatter(20, NameFormatter::ENGLISH_GREEDY);

echo $formatter->format('John Smith');                   // "John Smith"
echo $formatter->format('John William Smith');           // "John William Smith"
echo $formatter->format('John William Alexander Smith'); // "John William Smith" 
                                                         // (drops "Alexander")
```

#### 2. DENSE

Ensures every name part appears, at least as an initial. Better when all name components must be represented.

```php
// With 20 character limit
$formatter = new NameFormatter(20, NameFormatter::ENGLISH_DENSE);

echo $formatter->format('John William Alexander Smith'); // "John W. A. Smith"
                                                         // (includes all parts)
```

### For Arabic Names

Arabic names use a simple left-to-right approach, preserving whole words since initials don't make sense in Arabic.

```php
$formatter = new NameFormatter(25);

echo $formatter->format('محمد عبد الرحمن العبد الله'); // "محمد عبد الرحمن"
```

## Edge Cases Handled

### Very Long Names

The library handles unusually long names gracefully:

```php
$formatter = new NameFormatter(20);

// Long last name
echo $formatter->format('John Wolfeschlegelsteinhausenbergerdorff');
// "John Wolfeschlegelst"
```

### Whitespace Cleanup

No need to worry about extra spaces or inconsistent formatting:

```php
$formatter = new NameFormatter(20);
echo $formatter->format('  John   William   Smith  '); // "John William Smith"
```

## Configuration

```php
/**
 * @param int    $maxLength   Maximum allowed characters (≥ 1)
 * @param string $englishMode Strategy: ENGLISH_GREEDY or ENGLISH_DENSE
 * @param string $encoding    Character encoding (default: UTF-8)
 */
public function __construct(
    int    $maxLength   = 35,
    string $englishMode = self::ENGLISH_GREEDY,
    string $encoding    = 'UTF-8'
)
```

## Security & Compliance

This library only formats text - it doesn't store or transmit any personal data. It's designed with:
- GDPR considerations (no data retention)
- PCI DSS compatibility (no cardholder data storage)

## Requirements

- PHP 8.3+
- mbstring extension

## License

MIT License. See the [LICENSE](LICENSE.md) file.

## Credits

- [Omar Haris](https://github.com/omar-haris)

<!-- These keywords help with SEO but are invisible to users -->
<meta name="keywords" content="name formatting, ID card printing, credit card personalization, badge printing, PHP library, Arabic name formatting, card embossing, fixed-width name display, name truncation, smart name shortening">
