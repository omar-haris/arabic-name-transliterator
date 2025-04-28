# Arabic Name Transliterator

A powerful PHP library for transliterating Arabic names to English with high accuracy. Perfect for ID systems, passports, and any application needing reliable Arabic-to-English name conversion.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/omar-haris/arabic-name-transliterator.svg)](https://packagist.org/packages/omar-haris/arabic-name-transliterator)
[![Tests](https://github.com/omar-haris/arabic-name-transliterator/actions/workflows/tests.yml/badge.svg)](https://github.com/omar-haris/arabic-name-transliterator/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/omar-haris/arabic-name-transliterator.svg)](https://packagist.org/packages/omar-haris/arabic-name-transliterator)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Total Downloads](https://img.shields.io/packagist/dt/omar-haris/arabic-name-transliterator.svg)](https://packagist.org/packages/omar-haris/arabic-name-transliterator)

## Why This Library?

Transliterating Arabic names to English is challenging due to multiple correct spellings and regional variations. This library solves that problem with:

- **Accurate transliteration** that follows standard conventions
- **Support for names with diacritics** (harakat)
- **Regional variations** (currently Iraqi mapping, with extensibility for other regions)
- **Smart word-level and character-level mapping**
- **Full customization** options to meet your specific needs

## Key Features

- **Dictionary-based approach** for common names and words
- **Fallback letter-by-letter mapping** for names not in the dictionary
- **Customizable regional mappings** for different Arabic dialects and standards
- **Clean handling of diacritics** (harakat) and special characters
- **Well-documented code** with comprehensive test coverage
- **Zero external dependencies** besides PHP's mbstring

## Installation

Simply run:

```bash
composer require omar-haris/arabic-name-transliterator
```

## Basic Usage

```php
use ArabicNameTransliterator\Transliterator;
use ArabicNameTransliterator\Mapping\IraqMapping;

// Create a transliterator with Iraqi name mapping
$transliterator = new Transliterator(new IraqMapping());

// Transliterate names
echo $transliterator->transliterate('محمد');                // "Muhammad"
echo $transliterator->transliterate('عبد الرحمن');          // "Abd Al-Rahman"
echo $transliterator->transliterate('فاطمة الزهراء');       // "Fatimah Al-Zahraa"

// Works with diacritics
echo $transliterator->transliterate('مُحَمَّد');            // "Muhammad"

// Disable capitalization
echo $transliterator->transliterate('محمد علي', false);     // "muhammad ali"
```

## Advanced Usage

### Creating Custom Mappings

You can create your own mappings by extending `BaseMapping`:

```php
use ArabicNameTransliterator\Mapping\BaseMapping;

class EgyptianMapping extends BaseMapping
{
    public function getFullWordMap(): array
    {
        return [
            'محمد' => 'Mohamed',  // Egyptian spelling (vs. Iraqi "Muhammad")
            'عبد الرحمن' => 'Abdelrahman', // Different format
            // Add more mappings...
        ];
    }
    
    public function getLetterMap(): array
    {
        return [
            'ج' => 'g',  // In Egyptian dialect, ج is pronounced as "g" not "j"
            // Define the rest of your letter map...
        ];
    }
}

$egyptianTransliterator = new Transliterator(new EgyptianMapping());
```

### Extending Existing Mappings

You can also extend an existing mapping:

```php
use ArabicNameTransliterator\Mapping\IraqMapping;

class CustomIraqMapping extends IraqMapping
{
    public function getFullWordMap(): array
    {
        $originalMap = parent::getFullWordMap();
        
        // Add or override specific entries
        $customEntries = [
            'محمد' => 'Mohammed', // Override the default 'Muhammad'
            'عبد العزيز' => 'Abdul Aziz', // New entry
        ];
        
        return array_merge($originalMap, $customEntries);
    }
}
```

## How It Works

The transliteration process follows these steps:

1. The input Arabic text is split into words
2. Each word is checked against the full-word dictionary
   - If found, the predefined transliteration is used
   - If not found, the word is transliterated letter by letter
3. The transliterated words are joined with spaces
4. By default, each word is capitalized (can be disabled)

## Available Mappings

Currently, the library includes:

- `IraqMapping`: Suitable for Iraqi Arabic names, following common standards for official documents

More regional mappings will be added in future releases. Contributions are welcome!

## Requirements

- PHP 8.3+
- mbstring extension

## License

MIT License. See the [LICENSE](LICENSE.md) file.

## Credits

- [Omar Haris](https://github.com/omar-haris)

<!-- These keywords help with SEO but are invisible to users -->
<meta name="keywords" content="arabic transliteration, name transliteration, arabic to english, arabic names, name conversion, PHP library, Iraq names, arabic to latin, name standardization, identity documents">
# arabic-name-transliterator
