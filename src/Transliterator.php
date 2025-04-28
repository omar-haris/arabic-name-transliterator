<?php
/**
 * This file is part of the Arabic Name Transliterator package.
 *
 * (c) Omar Haris <omar@haris.bz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that accompanies this source code.
 */

declare(strict_types=1);

namespace ArabicNameTransliterator;

use ArabicNameTransliterator\Enum\MappingType;
use ArabicNameTransliterator\Factory\MappingFactory;
use ArabicNameTransliterator\Mapping\BaseMapping;


class Transliterator
{
    /**
     * Enable sun letter assimilation
     * 
     * @var bool Whether to apply sun letter assimilation
     */
    private bool $enableSunLetterAssimilation;

    /**
     * @var string How to transliterate final Ta Marbuta:
     *             - "ah" (Fatimah)
     *             - "a"  (Fatima)
     *             - "at" (Fatimat) used in some academic transliterations
     */
    private string $taMarbutaStyle;

    /**
     * Sun letters in Arabic (for assimilation of the definite article "ال").
     * e.g., "التّفاح" → "at-tufah"
     * 
     * @var array<string> The sun letters in Arabic
     */
    private const SUN_LETTERS = [
        'ت', 'ث', 'د', 'ذ', 'ر', 'ز', 'س', 'ش',
        'ص', 'ض', 'ط', 'ظ', 'ل', 'ن'
    ];

    /**
     * The mapping to use for transliteration.
     * 
     * @var BaseMapping The mapping to use for transliteration
     */
    private BaseMapping $mapping;

    /**
     * Creates a new Transliterator with the specified mapping strategy.
     *
     * This constructor is flexible - it accepts either:
     * - A MappingType enum value (recommended for most use cases)
     * - A direct BaseMapping instance (for advanced custom mappings)
     *
     * @param string $taMarbutaStyle The style for transliterating the final Ta Marbuta ("ة")
     * @param MappingType|BaseMapping $mapping The mapping or mapping type to use for transliteration
     * @param bool $enableSunLetterAssimilation Whether to enable sun letter assimilation
     */
    public function __construct(MappingType|BaseMapping $mapping = MappingType::DEFAULT, string $taMarbutaStyle = 'ah', bool $enableSunLetterAssimilation = true)
    {
        if ($mapping instanceof MappingType) {
            $this->mapping = MappingFactory::create($mapping);
        } else {
            $this->mapping = $mapping;
        }

        $this->taMarbutaStyle = $taMarbutaStyle;
        $this->enableSunLetterAssimilation = $enableSunLetterAssimilation;
    }

    /**
     * Gets the current mapping being used by the transliterator.
     *
     * @return BaseMapping The current mapping instance
     */
    public function getMapping(): BaseMapping
    {
        return $this->mapping;
    }

    /**
     * Sets a new mapping to be used by the transliterator.
     *
     * @param MappingType|BaseMapping $mapping The new mapping or mapping type to use
     * 
     * @return self For method chaining
     */
    public function setMapping(MappingType|BaseMapping $mapping): self
    {
        if ($mapping instanceof MappingType) {
            $this->mapping = MappingFactory::create($mapping);
        } else {
            $this->mapping = $mapping;
        }
        
        return $this;
    }

    /**
     * Checks whether sun letter assimilation is enabled.
     * 
     * @return bool True if sun letter assimilation is enabled, false otherwise
     */
    public function isSunLetterAssimilationEnabled(): bool
    {
        return $this->enableSunLetterAssimilation;
    }

    /**
     * Enables or disables sun letter assimilation.
     * 
     * @param bool $enable Whether to enable or disable sun letter assimilation
     * @return self For method chaining
     */
    public function setSunLetterAssimilation(bool $enable): self
    {
        $this->enableSunLetterAssimilation = $enable;
        return $this;
    }

    /**
     * Gets the current Ta Marbuta style setting.
     * 
     * @return string The current Ta Marbuta style
     */
    public function getTaMarbutaStyle(): string
    {
        return $this->taMarbutaStyle;
    }

    /**
     * Sets how Ta Marbuta should be transliterated at word endings.
     * 
     * @param string $style "ah", "a", or "at"
     * @return self For method chaining
     */
    public function setTaMarbutaStyle(string $style): self
    {
        $this->taMarbutaStyle = $style;
        return $this;
    }

    /**
     * Transliterates the given Arabic text to English.
     *
     * Steps:
     *   1) If exact dictionary match, use it;
     *   2) Else letter-by-letter mapping;
     *   3) Apply sun-letter assimilation if enabled;
     *   4) Handle final ta marbuta style if needed;
     *   5) Clean spacing & optionally capitalize each word.
     *
     * @param string $arabicText      The Arabic text to transliterate
     * @param bool   $capitalizeWords Whether to capitalize each word
     *
     * @return string The transliterated result
     */
    public function transliterate(string $arabicText, bool $capitalizeWords = true): string
    {
        // Handle empty input
        if (trim($arabicText) === '') {
            return '';
        }
        
        // Split on spaces (basic tokenization)
        $words = explode(' ', trim($arabicText));

        $transliteratedParts = [];
        $fullWordMap = $this->mapping->getFullWordMap();
        $letterMap   = $this->mapping->getLetterMap();

        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') {
                continue;
            }

            // 1) Check dictionary
            if (array_key_exists($word, $fullWordMap)) {
                $mapped = $fullWordMap[$word];
            } else {
                // 2) Letter-by-letter fallback
                $mapped = $this->letterByLetter($word, $letterMap);

                // 3) (Optional) If the word starts with "ال" + sun letter, do assimilation
                if ($this->enableSunLetterAssimilation) {
                    // Only do assimilation if not matched by dictionary
                    $mapped = $this->applySunLetterAssimilation($word, $mapped, $letterMap);
                }

                // 4) Handle final ta marbuta if the original Arabic word ends with "ة"
                //    (only if not in dictionary, which might override).
                if ($this->endsWithTaMarbuta($word)) {
                    $mapped = $this->applyTaMarbutaStyle($mapped);
                }
            }

            $transliteratedParts[] = $mapped;
        }

        // Join words with single space
        $transliterated = implode(' ', $transliteratedParts);

        // Cleanup spacing
        $transliterated = preg_replace('/\s+/', ' ', $transliterated) ?? $transliterated;
        $transliterated = trim($transliterated);

        // Optionally capitalize each word
        if ($capitalizeWords) {
            $transliterated = $this->capitalizeWords($transliterated);
        }

        return $transliterated;
    }

    /**
     * Character-by-character mapping using the letter map.
     * 
     * @param string $arabicWord The Arabic word to transliterate
     * @param array $letterMap The letter map
     * @return string The transliterated result
     */
    private function letterByLetter(string $arabicWord, array $letterMap): string
    {
        $result = '';
        $length = mb_strlen($arabicWord);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($arabicWord, $i, 1);
            $result .= $letterMap[$char] ?? $char; // fallback: keep unknown chars
        }

        return $result;
    }

    /**
     * Sun-letter assimilation:
     *
     * If an Arabic word starts with "ال" followed by a sun letter,
     * we remove the "l" in "al", and we double the next letter
     * in the transliteration.
     *
     * Example: "الرحمن" => "ar-Rahman"
     *          "الشمس"  => "ash-Shams"
     *
     * BUT note that the dictionary might have overridden it (e.g. "الرحمن" => "Al-Rahman"),
     * in which case that override is used. So assimilation only applies in fallback mode.
     * 
     * @param string $originalArabic The original Arabic word
     * @param string $mapped The mapped word
     * @param array $letterMap The letter map
     * @return string The mapped word with the applied assimilation
     */
    private function applySunLetterAssimilation(string $originalArabic, string $mapped, array $letterMap): string
    {
        // 1) Check if original starts with "ال" + sun letter
        //    (We actually check the 2nd letter to see if it's a sun letter.)
        if (mb_substr($originalArabic, 0, 2) === 'ال' && mb_strlen($originalArabic) > 2) {
            // The 3rd character might be a sun letter
            $thirdChar = mb_substr($originalArabic, 2, 1);
            if (in_array($thirdChar, self::SUN_LETTERS, true)) {
                /*
                 * Our letter-by-letter translation for "ال" typically yields "al".
                 * We'll transform "al<sunLetter>" => "a<sunLetter>-<sunLetter>..."
                 * e.g., "alr" => "ar-r"...
                 *
                 * Implementation approach:
                 * - find the substring "al" at the start
                 * - remove the "l"
                 * - double the next letter
                 * - insert a hyphen
                 */

                // Confirm that the fallback mapping gave us something like "al" at the start
                if (mb_substr($mapped, 0, 2) === 'al') {
                    // "ar-Rahman", "ash-Shams", ...
                    // We need the next letter (after 'al'), which is the transliteration of the sun letter
                    $afterAl = mb_substr($mapped, 2);

                    // Typically the next letter might be 'r' if the Arabic letter was 'ر', etc.
                    $firstLetter = mb_substr($afterAl, 0, 1);
                    $rest        = mb_substr($afterAl, 1);

                    // So we produce "a" + <firstLetter> + "-" + <firstLetter> + <rest>
                    $mapped = 'a' . $firstLetter . '-' . $firstLetter . $rest;
                }
            }
        }

        return $mapped;
    }

    /**
     * Checks if the original Arabic word ends with Ta Marbuta ("ة").
     * 
     * @param string $arabicWord The Arabic word to check
     * @return bool True if the word ends with "ة", false otherwise
     */
    private function endsWithTaMarbuta(string $arabicWord): bool
    {
        $lastChar = mb_substr($arabicWord, -1);
        return ($lastChar === 'ة');
    }

    /**
     * Applies the user-chosen style for final Ta Marbuta,
     * e.g., "ah", "a", "at". We assume our letter map returns "h"
     * for "ة" by default. So if the mapped word ends in "h", we replace
     * it with the user's style (like "ah" → "ah", or "a").
     * 
     * @param string $mappedWord The mapped word to apply the style to
     * @return string The mapped word with the applied style
     */
    private function applyTaMarbutaStyle(string $mappedWord): string
    {
        // If the mapped word ends in "h"
        if (mb_substr($mappedWord, -1) === 'h') {
            // Remove the final 'h'
            $base = mb_substr($mappedWord, 0, -1);

            return match ($this->taMarbutaStyle) {
                'ah' => $base . 'ah',
                'a'  => $base . 'a',
                'at' => $base . 'at',
                default => $mappedWord // fallback if user gave something unexpected
            };
        }

        return $mappedWord;
    }

    /**
     * Capitalizes each word in the string by spaces.
     * 
     * @param string $text The text to capitalize
     * @return string The text with each word capitalized
     */
    private function capitalizeWords(string $text): string
    {
        $words = explode(' ', $text);
        $capitalized = array_map(static fn ($item) => ucfirst($item), $words);
        return implode(' ', $capitalized);
    }
}
