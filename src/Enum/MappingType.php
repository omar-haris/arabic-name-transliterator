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

namespace ArabicNameTransliterator\Enum;

/**
 * Enum for specifying which mapping type to use for transliteration.
 * 
 * This enum defines the available regional/cultural transliteration mappings.
 * When new mapping implementations are added, they should be listed here.
 */
enum MappingType: string
{
    /**
     * Iraqi standard mapping - more formal, preserves compound names
     */
    case IRAQI = 'iraqi';
    
    /**
     * Default mapping using Iraqi standards as baseline
     */
    case DEFAULT = 'default';
} 