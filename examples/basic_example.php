<?php
/**
 * This file is part of the Arabic Name Transliterator package.
 *
 * (c) Omar Haris <omar@haris.bz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that accompanies this source code.
 */
require 'vendor/autoload.php';

use ArabicNameTransliterator\Transliterator;
use ArabicNameTransliterator\Mapping\IraqMapping;

$transliterator = new Transliterator(new IraqMapping());

// Example #1: A single name that has a direct mapping
$input1 = 'عبدالله';
$result1 = $transliterator->transliterate($input1);
echo $result1 . PHP_EOL;
// Outputs: "Abdullah"

// Example #2: Compound name with separate words in the dictionary
$input2 = 'عبد الرحمن';
$result2 = $transliterator->transliterate($input2);
echo $result2 . PHP_EOL;
// Outputs: "Abd Al-Rahman"

// Example #3: A name partially in dictionary, partially not
$input3 = 'عبد الخالق';
// 'عبد' is in dictionary → "Abd"
// 'الخالق' is not → letter-by-letter fallback
$result3 = $transliterator->transliterate($input3);
echo $result3 . PHP_EOL;
// Possible output: "Abd Alkhaleq" (depending on letter map)

// Example #4: Disabling capitalization
$input4 = 'محمد علي';
$result4 = $transliterator->transliterate($input4, false);
echo $result4 . PHP_EOL;
// Outputs: "Muhammad Ali" exactly as found in the dictionary (here it matches both words).
