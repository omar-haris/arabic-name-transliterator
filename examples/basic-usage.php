<?php
/**
 * This file is part of the card-name-fit package.
 *
 * (c) Omar Haris <omar@haris.bz>
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CardNameFit\NameFormatter;

// Example 1: Basic usage with default settings
echo "Example 1: Basic usage with default settings\n";
echo "--------------------------------------------\n";
$formatter = new NameFormatter();
$examples = [
    'John Smith',
    'John William Smith',
    'John William Alexander Smith',
    'محمد عبد الله',
    'محمد عبد الرحمن العبد الله',
];

foreach ($examples as $name) {
    echo "Original: \"$name\"\n";
    echo "Formatted: \"" . $formatter->format($name) . "\"\n\n";
}

// Example 2: Using the ENGLISH_DENSE strategy with a smaller limit
echo "Example 2: ENGLISH_DENSE strategy with length=20\n";
echo "-----------------------------------------------\n";
$denseFormatter = new NameFormatter(20, NameFormatter::ENGLISH_DENSE);
$englishExamples = [
    'John Smith',
    'John William Smith',
    'John William Alexander Smith',
    'John William Alexander Johnson Smith',
];

foreach ($englishExamples as $name) {
    echo "Original: \"$name\"\n";
    echo "Formatted: \"" . $denseFormatter->format($name) . "\"\n\n";
}

// Example 3: Comparing strategies with the same name
echo "Example 3: Comparing strategies\n";
echo "-----------------------------\n";
$name = 'John William Alexander Robert Smith';

echo "Original name: \"$name\"\n";
echo "Length: " . mb_strlen($name) . " characters\n\n";

$limits = [35, 30, 25, 20, 15];
echo "ENGLISH_GREEDY strategy:\n";

foreach ($limits as $limit) {
    $greedyFormatter = new NameFormatter($limit, NameFormatter::ENGLISH_GREEDY);
    $result = $greedyFormatter->format($name);
    echo "- Limit $limit: \"$result\" (" . mb_strlen($result) . " chars)\n";
}

echo "\nENGLISH_DENSE strategy:\n";
foreach ($limits as $limit) {
    $denseFormatter = new NameFormatter($limit, NameFormatter::ENGLISH_DENSE);
    $result = $denseFormatter->format($name);
    echo "- Limit $limit: \"$result\" (" . mb_strlen($result) . " chars)\n";
}

// Example 4: Edge cases
echo "\nExample 4: Edge Cases\n";
echo "------------------\n";

$edgeFormatter = new NameFormatter(15);
$edgeCases = [
    'Single' => 'Wolfeschlegelsteinhausenbergerdorff',
    'VeryLongFirst' => 'Wolfeschlegelsteinhausenbergerdorff John',
    'VeryLongLast' => 'John Wolfeschlegelsteinhausenbergerdorff',
    'WithExtraSpaces' => '  John   William   Smith  ',
];

foreach ($edgeCases as $type => $name) {
    echo "$type:\n";
    echo "Original: \"$name\"\n";
    echo "Formatted: \"" . $edgeFormatter->format($name) . "\"\n\n";
} 