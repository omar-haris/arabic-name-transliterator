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

namespace ArabicNameTransliterator\Factory;

use ArabicNameTransliterator\Enum\MappingType;
use ArabicNameTransliterator\Mapping\BaseMapping;
use ArabicNameTransliterator\Mapping\IraqMapping;

/**
 * Factory class to create mapping instances based on MappingType enum.
 * 
 * This factory centralizes the creation of all mapping implementations,
 * making it easy to add new mapping types without modifying the Transliterator.
 */
class MappingFactory
{
    /**
     * Creates and returns a mapping instance based on the provided MappingType.
     * 
     * If the mapping type is not found, it will fallback to the default mapping (currently Iraqi).
     *
     * @param MappingType $mappingType The type of mapping to create
     * 
     * @return BaseMapping The corresponding mapping implementation
     */
    public static function create(MappingType $mappingType): BaseMapping
    {
        return match($mappingType) {
            MappingType::IRAQI, 
            MappingType::DEFAULT => new IraqMapping(),
            
            // Add more cases here as new mapping implementations are added
            // Example: MappingType::EGYPTIAN => new EgyptianMapping(),
        };
    }
} 