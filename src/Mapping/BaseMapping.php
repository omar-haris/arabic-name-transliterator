<?php
/**
 * This file is part of the Arabic Name Transliterator package.
 *
 * (c) Omar Haris <omar@haris.bz>
 * 
 * @license MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that accompanies this source code.
 */

declare(strict_types=1);

namespace ArabicNameTransliterator\Mapping;

/**
 * Base abstract class for defining transliteration mappings.
 *
 * This abstract class defines the interface that all mapping strategies
 * must implement, providing both:
 * 1. Full-word mappings for accurate, culturally-appropriate transliteration of common names
 * 2. Letter-by-letter mappings as a fallback for words not in the full-word dictionary
 *
 * Extend this class to create new mapping strategies for different:
 * - Regional variants (Egyptian, Syrian, Gulf, etc.)
 * - Formal standards (ICAO, Library of Congress, etc.)
 * - Personal preferences or organization-specific requirements
 *
 * @author Omar Haris <omar@haris.bz>
 */
abstract class BaseMapping
{
    /**
     * Returns full-word specific replacements for common Arabic names and name components.
     *
     * This dictionary-based approach provides more accurate and culturally appropriate
     * transliterations than what could be achieved through character-by-character mapping.
     * It's especially important for names that have established transliteration conventions.
     *
     * Example:
     * [
     *   'عبد' => 'Abd',
     *   'محمد' => 'Muhammad',
     *   'عبد الرحمن' => 'Abd Al-Rahman'
     * ]
     *
     * @return array<string, string> Mapping of Arabic words to their English transliterations
     */
    abstract public function getFullWordMap(): array;

    /**
     * Returns the mapping of single Arabic letters/characters to their English equivalents.
     *
     * This mapping is used as a fallback when a word is not found in the full-word dictionary.
     * It should include:
     * - All Arabic consonants and vowels
     * - Various forms of hamza
     * - Diacritics (usually mapped to empty string)
     * - Any special characters relevant to names
     *
     * The mapping should follow common transliteration standards but may vary
     * based on regional preferences or formal standards.
     *
     * @return array<string, string> Mapping of Arabic characters to English equivalents
     */
    abstract public function getLetterMap(): array;
}
