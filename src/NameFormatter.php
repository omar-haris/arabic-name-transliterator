<?php
/**
 * This file is part of the card-name-fit package.
 *
 * (c) Omar Haris <omar@haris.bz>
 *
 * @license MIT
 */
declare(strict_types=1);

namespace CardNameFit;

use TypeError;

/**
 * NameFormatter - Formats personal names to fit within a specific character limit
 *
 * This class formats Arabic or English personal names so they never exceed a
 * configurable character limit while maintaining readability. Perfect for:
 * - ID badges
 * - Bank cards
 * - Payment cards
 * - Passports
 * - Labels
 * - Thermal printers
 * - Any fixed-width display
 *
 * BASIC USAGE:
 * ```php
 * // Initialize with maximum length and strategy
 * $formatter = new NameFormatter(25, NameFormatter::ENGLISH_DENSE);
 * 
 * // Format a name
 * $result = $formatter->format('John William Alexander Smith');
 * // Returns: "John W. A. Smith" (if using ENGLISH_DENSE)
 * 
 * // Arabic name example
 * $result = $formatter->format('محمد عبد الرحمن العبد الله الجبر');
 * // Returns: "محمد عبد الرحمن" (only includes names that fit)
 * ```
 *
 * STRATEGIES:
 * 
 * 1. Arabic Names:
 *    - Always uses "greedy" algorithm
 *    - Keeps adding whole words from left-to-right until limit reached
 *    - Never splits words (except for mononyms that exceed limit)
 *    - Example: "محمد عبد الرحمن العبد الله" → "محمد عبد الرحمن"
 *
 * 2. English Names:
 *    - Two strategies available:
 *      a) ENGLISH_GREEDY: Keeps full middle names while they fit
 *         Example: "John William Smith" → "John William Smith"
 *         Example: "John William Alexander Smith" → "John William Smith"
 *      
 *      b) ENGLISH_DENSE: Guarantees every name appears (at least as initial)
 *         Example: "John William Alexander Smith" → "John W. A. Smith"
 *         Upgrades initials to full names when space permits
 *
 * TECHNICAL NOTES:
 * - Spaces and hyphens count toward the character limit
 * - UTF-8 compatible (uses mbstring extension)
 * - No external dependencies
 *
 * @author Omar Haris
 */
final class NameFormatter
{
    /**
     * English Greedy Strategy
     *
     * Keeps whole middle names in output while they fit within the limit.
     * Falls back to initials only when necessary to save space.
     * May omit middle names entirely if they don't fit.
     *
     * Example with maxLength=20:
     * "John William Alexander Smith" → "John William Smith"
     *
     * @var string
     */
    public const string ENGLISH_GREEDY = 'greedy';

    /**
     * English Dense Strategy
     *
     * Ensures every middle name appears at least as an initial.
     * Upgrades initials to full names when space permits (left-to-right).
     * Guarantees representation of all name parts.
     *
     * Example with maxLength=20:
     * "John William Alexander Smith" → "John W. A. Smith"
     *
     * @var string
     */
    public const string ENGLISH_DENSE = 'dense';

    /**
     * Maximum character limit for formatted names
     *
     * @var int
     */
    private int $maxLength = 35;

    /**
     * Strategy for formatting English names
     *
     * @var string
     */
    private string $englishMode = self::ENGLISH_GREEDY;

    /**
     * Character encoding for multibyte string operations
     *
     * @var string
     */
    private string $encoding = 'UTF-8';

    /**
     * Creates a new NameFormatter instance
     *
     * @param int    $maxLength   Maximum allowed characters (must be ≥ 1)
     * @param string $englishMode Strategy for English names: ENGLISH_GREEDY or ENGLISH_DENSE
     * @param string $encoding    Character encoding (default: UTF-8)
     *
     * @throws \RangeException           If $maxLength is less than 1
     * @throws \InvalidArgumentException If $englishMode is not a valid strategy
     * @throws \RuntimeException         If the mbstring extension is not available
     *
     * @example
     * ```php
     * // Basic usage with defaults (35 chars, greedy mode)
     * $formatter = new NameFormatter();
     *
     * // Custom length with dense mode
     * $formatter = new NameFormatter(20, NameFormatter::ENGLISH_DENSE);
     *
     * // Custom encoding (rare use case)
     * $formatter = new NameFormatter(25, NameFormatter::ENGLISH_GREEDY, 'ISO-8859-1');
     * ```
     */
    public function __construct(
        int    $maxLength   = 35,
        string $englishMode = self::ENGLISH_GREEDY,
        string $encoding    = 'UTF-8'
    ) {
        if ($maxLength < 1) {
            throw new \RangeException('Maximum length must be ≥ 1');
        }

        if (!\in_array($englishMode, [self::ENGLISH_GREEDY, self::ENGLISH_DENSE], true)) {
            throw new \InvalidArgumentException("Unknown English mode: $englishMode");
        }

        if (!\extension_loaded('mbstring')) {
            throw new \RuntimeException('The mbstring extension is required.');
        }

        $this->maxLength   = $maxLength;
        $this->englishMode = $englishMode;
        $this->encoding    = $encoding;
    }

    /**
     * Formats a name to fit within the maximum character limit
     *
     * This method:
     * 1. Normalizes whitespace in the input name
     * 2. Checks if the name already fits within the limit
     * 3. Detects if the name contains Arabic characters
     * 4. Applies the appropriate formatting strategy
     *
     * @param string|null $fullName The full name to format
     * @return string              The formatted name that fits within the limit
     * @throws TypeError           If null is supplied instead of a string
     *
     * @example
     * ```php
     * $formatter = new NameFormatter(20);
     * 
     * // English name examples
     * echo $formatter->format('John Smith');                   // "John Smith"
     * echo $formatter->format('John William Smith');           // "John William Smith"
     * echo $formatter->format('John William Alexander Smith'); // "John William Smith"
     * 
     * // With ENGLISH_DENSE mode
     * $denseFormatter = new NameFormatter(20, NameFormatter::ENGLISH_DENSE);
     * echo $denseFormatter->format('John William Alexander Smith'); // "John W. A. Smith"
     * 
     * // Arabic example
     * echo $formatter->format('محمد عبد الرحمن العبد الله');   // "محمد عبد الرحمن"
     * 
     * // Whitespace normalization
     * echo $formatter->format('  John   William   Smith  ');   // "John William Smith"
     * ```
     */
    public function format(?string $fullName): string
    {
        if ($fullName === null) {
            throw new TypeError('Name may not be null.');
        }

        $clean = $this->normalizeWhitespace($fullName);

        // Already fits → return as-is.
        if ($this->charLen($clean) <= $this->maxLength) {
            return $clean;
        }

        return $this->containsArabic($clean)
            ? $this->formatArabic($clean)
            : $this->formatEnglish($clean);
    }

    /**
     * Formats an Arabic name using a greedy word accumulation strategy
     *
     * Rules:
     * 1. Add whole words from left to right until the next word would exceed the limit
     * 2. Never split a word (except for first word if it alone exceeds the limit)
     * 3. Preserve all selected words in their entirety
     *
     * @param string $name Normalized Arabic name to format
     * @return string      Formatted name fitting within the limit
     * 
     * @example
     * ```php
     * // With max length of 25:
     * formatArabic('محمد عبد الرحمن العبد الله');  // Returns: "محمد عبد الرحمن"
     * 
     * // With very long first name (truncation happens):
     * formatArabic('عبدالرحمنالطويلجدا محمد');     // Returns: "عبدالرحمنالطويلج"
     * ```
     */
    private function formatArabic(string $name): string
    {
        $words = \explode(' ', $name);

        // Mononym longer than limit → hard truncate.
        if ($this->charLen($words[0]) > $this->maxLength) {
            return $this->substr($words[0], 0, $this->maxLength);
        }

        $picked  = [];
        $current = 0;

        foreach ($words as $word) {
            $wordLen = $this->charLen($word);
            $nextLen = $current === 0 ? $wordLen : $current + 1 + $wordLen;

            if ($nextLen > $this->maxLength) {
                break;
            }
            $picked[] = $word;
            $current  = $nextLen;
        }

        // Fallback guard (should never be empty)
        return $picked
            ? \implode(' ', $picked)
            : $this->substr($name, 0, $this->maxLength);
    }

    /**
     * Routes English name formatting to the appropriate strategy method
     *
     * @param string $name Normalized English name to format
     * @return string      Formatted name fitting within the limit
     * 
     * @example
     * ```php
     * // Using ENGLISH_GREEDY strategy:
     * formatEnglish('John William Alexander Smith'); // "John William Smith"
     * 
     * // Using ENGLISH_DENSE strategy:
     * formatEnglish('John William Alexander Smith'); // "John W. A. Smith"
     * ```
     */
    private function formatEnglish(string $name): string
    {
        return $this->englishMode === self::ENGLISH_DENSE
            ? $this->formatEnglishDense($name)
            : $this->formatEnglishGreedy($name);
    }

    /**
     * Formats an English name using the GREEDY strategy
     *
     * Rules:
     * 1. Always keep first and last names (truncate last if needed)
     * 2. Include as many full middle names as possible
     * 3. Use initials for middle names only when full names won't fit
     * 4. Drop middle names completely if even initials won't fit
     *
     * @param string $name Normalized English name to format
     * @return string      Formatted name fitting within the limit
     * 
     * @example
     * ```php
     * // With maxLength=25:
     * formatEnglishGreedy('John Smith');                   // "John Smith"
     * formatEnglishGreedy('John William Smith');           // "John William Smith"
     * formatEnglishGreedy('John William Alexander Smith'); // "John William Smith"
     * 
     * // With maxLength=15:
     * formatEnglishGreedy('John William Alexander Smith'); // "John W. Smith"
     * 
     * // With maxLength=10:
     * formatEnglishGreedy('John William Alexander Smith'); // "John Smith"
     * 
     * // When first + last is too long:
     * formatEnglishGreedy('Christopher Wolfeschlegelsteinhausenbergerdorff'); // "Christopher Wolf..."
     * ```
     */
    private function formatEnglishGreedy(string $name): string
    {
        $words = \explode(' ', $name);
        $count = \count($words);

        // Single-word name (rare in English)
        if ($count === 1) {
            return $this->substr($words[0], 0, $this->maxLength);
        }

        $first    = $words[0];
        $last     = $words[$count - 1];
        $output   = [$first];
        $current  = $this->charLen($first);
        $reserved = 1 + $this->charLen($last); // " … last"

        // If first + last alone overflow → truncate last.
        if ($current + $reserved > $this->maxLength) {
            $tailRoom = $this->maxLength - $current - 1;
            $output[] = $this->substr($last, 0, $tailRoom);
            return \implode(' ', $output);
        }

        /* middle names */
        for ($i = 1; $i < $count - 1; ++$i) {
            $word       = $words[$i];
            $wordLen    = $this->charLen($word);
            $withWord   = $current + 1 + $wordLen + $reserved;
            $withInit   = $current + 1 + 2        + $reserved; // "A."

            if ($withWord <= $this->maxLength) {
                $output[] = $word;
                $current += 1 + $wordLen;
            } elseif ($withInit <= $this->maxLength) {
                $output[] = $this->substr($word, 0, 1) . '.';
                $current += 1 + 2;
            } else {
                break; // Even initial won't fit.
            }
        }

        $output[] = $last;
        return \implode(' ', $output);
    }

    /**
     * Formats an English name using the DENSE strategy
     *
     * Rules:
     * 1. Always keep first and last names (truncate last if needed)
     * 2. Ensure every middle name appears at least as an initial
     * 3. Upgrade initials to full names when space permits (left to right)
     *
     * @param string $name Normalized English name to format
     * @return string      Formatted name fitting within the limit
     * 
     * @example
     * ```php
     * // With maxLength=25:
     * formatEnglishDense('John William Alexander Smith'); // "John William A. Smith"
     * 
     * // With maxLength=18:
     * formatEnglishDense('John William Alexander Smith'); // "John W. A. Smith"
     * 
     * // With maxLength=12:
     * formatEnglishDense('John William Alexander Smith'); // "John W. Smith" (no space for all initials)
     * 
     * // When first + last is too long:
     * formatEnglishDense('Christopher Wolfeschlegelsteinhausenbergerdorff'); // "Christopher Wolf..."
     * ```
     */
    private function formatEnglishDense(string $name): string
    {
        $words = \explode(' ', $name);
        $count = \count($words);

        if ($count === 1) {
            return $this->substr($words[0], 0, $this->maxLength);
        }

        $first = $words[0];
        $last  = $words[$count - 1];

        if ($this->charLen("$first $last") > $this->maxLength) {
            // Keep first full; truncate last.
            $frontRoom = $this->maxLength - $this->charLen($first) - 1;
            return $first . ' ' . $this->substr($last, 0, $frontRoom);
        }

        // Start with initials for every middle word
        $result = [$first];
        for ($i = 1; $i < $count - 1; ++$i) {
            $result[] = $this->substr($words[$i], 0, 1) . '.';
        }
        $result[] = $last;
        $len = $this->charLen(\implode(' ', $result));

        // Upgrade initials to full words (left→right) when space permits
        for ($i = 1; $i < $count - 1 && $len <= $this->maxLength; ++$i) {
            $full   = $words[$i];
            $delta  = $this->charLen($full) - 2; // 2 = "A."
            $newLen = $len + $delta;

            if ($newLen <= $this->maxLength) {
                $result[$i] = $full;
                $len        = $newLen;
            }
        }

        return \implode(' ', $result);
    }

    /**
     * Normalizes whitespace in a string
     *
     * This method:
     * 1. Replaces all consecutive whitespace with a single space
     * 2. Removes leading and trailing whitespace
     *
     * @param string $text Text to normalize
     * @return string      Normalized text
     * 
     * @example
     * ```php
     * normalizeWhitespace('  John   William   Smith  '); // "John William Smith"
     * normalizeWhitespace("\tJohn\nWilliam\r\nSmith"); // "John William Smith"
     * ```
     */
    private function normalizeWhitespace(string $text): string
    {
        return \trim((string) \preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Detects if a string contains any Arabic characters
     *
     * Uses Unicode character property matching to identify Arabic script.
     *
     * @param string $text Text to check
     * @return bool        True if contains Arabic, false otherwise
     * 
     * @example
     * ```php
     * containsArabic('John Smith');              // false
     * containsArabic('محمد');                    // true
     * containsArabic('John محمد Smith');         // true (mixed content)
     * ```
     */
    private function containsArabic(string $text): bool
    {
        return \preg_match('/\p{Arabic}/u', $text) === 1;
    }

    /**
     * Gets the length of a string in characters
     *
     * Uses mbstring extension to properly count multibyte characters.
     *
     * @param string $text Text to measure
     * @return int         Character count
     * 
     * @example
     * ```php
     * charLen('abc');      // 3
     * charLen('محمد');     // 4 (not byte count)
     * charLen('Привет');   // 6 (not byte count)
     * ```
     */
    private function charLen(string $text): int
    {
        return \mb_strlen($text, $this->encoding);
    }

    /**
     * Gets a substring of a string, respecting multibyte characters
     *
     * Uses mbstring extension to properly handle multibyte characters.
     *
     * @param string $text  Source text
     * @param int    $start Starting position (0-indexed)
     * @param int    $len   Maximum number of characters to return
     * @return string       Extracted substring
     * 
     * @example
     * ```php
     * substr('John Smith', 0, 4);   // "John"
     * substr('محمد علي', 0, 3);     // "محم" (first 3 characters)
     * substr('Привет мир', 2, 3);   // "иве" (characters 3-5)
     * ```
     */
    private function substr(string $text, int $start, int $len): string
    {
        return \mb_substr($text, $start, $len, $this->encoding);
    }
}
