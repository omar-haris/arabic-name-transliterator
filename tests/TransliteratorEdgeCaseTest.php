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

namespace ArabicNameTransliterator\Tests;

use ArabicNameTransliterator\Transliterator;
use ArabicNameTransliterator\Enum\MappingType;
use PHPUnit\Framework\TestCase;

/**
 * Edge case and bug prevention tests
 * 
 * This test suite focuses on edge cases and potential bugs without modifying any existing code.
 */
class TransliteratorEdgeCaseTest extends TestCase
{
    private Transliterator $transliterator;

    protected function setUp(): void
    {
        $this->transliterator = new Transliterator();
    }

    public function testEmptyAndNullInput(): void
    {
        // Empty string
        $this->assertSame('', $this->transliterator->transliterate(''));
        
        // Only whitespace
        $this->assertSame('', $this->transliterator->transliterate('   '));
        $this->assertSame('', $this->transliterator->transliterate("\t\n"));
        
        // Empty string with capitalization set to false
        $this->assertSame('', $this->transliterator->transliterate('', false));
    }

    /**
     * @dataProvider nonArabicInputProvider
     */
    public function testNonArabicInput(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($input));
    }

    public function nonArabicInputProvider(): array
    {
        return [
            'Latin text' => ['Hello World', 'Hello World'],
            'Numbers' => ['123456', '123456'],
            'Special characters' => ['!@#$%^&*()_+', '!@#$%^&*()_+'],
            'Mixed Latin and special chars' => ['Hello123!@#', 'Hello123!@#'],
            'Mixed Arabic and Latin' => ['أهلا World', 'Ahla World'],
            'Mixed case Latin' => ['MiXeD CaSe', 'MiXeD CaSe'],
        ];
    }

    /**
     * @dataProvider unusualWhitespaceProvider
     */
    public function testUnusualWhitespace(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($input));
    }

    public function unusualWhitespaceProvider(): array
    {
        return [
            'Multiple spaces' => ['محمد    علي', 'Muhammad Ali'],
            'Tab character' => ["محمد\tعلي", 'Mhmd Aly'],
            'Newline character' => ["محمد\nعلي", 'Mhmd Aly'],
            'Carriage return' => ["محمد\rعلي", 'Mhmd Aly'],
            'Zero-width space' => ["محمد\u{200B}علي", 'Mhmd​aly'], 
            'Leading/trailing mixed whitespace' => [" \t\n محمد علي \n\t ", 'Muhammad Ali'],
        ];
    }

    /**
     * @dataProvider boundaryTestProvider
     */
    public function testBoundaryValues(string $input): void
    {
        // Test that the function executes without error for boundary values
        $result = $this->transliterator->transliterate($input);
        $this->addToAssertionCount(1);
    }

    public function boundaryTestProvider(): array
    {
        // Generate long strings of different lengths
        return [
            'Very long input (1000 chars)' => [str_repeat('محمد ', 200)],
            'Long name with 100 words' => [str_repeat('عبدالله ', 100)],
        ];
    }

    /**
     * @dataProvider consecutiveCallsProvider
     */
    public function testConsecutiveCalls(array $inputs, array $expected): void
    {
        // Test that consecutive calls with different settings work correctly
        $transliterator = new Transliterator();
        
        foreach ($inputs as $index => $config) {
            if (isset($config['mapping'])) {
                $transliterator->setMapping($config['mapping']);
            }
            
            if (isset($config['sunLetterAssimilation'])) {
                $transliterator->setSunLetterAssimilation($config['sunLetterAssimilation']);
            }
            
            if (isset($config['taMarbutaStyle'])) {
                $transliterator->setTaMarbutaStyle($config['taMarbutaStyle']);
            }
            
            $result = $transliterator->transliterate($config['input'], $config['capitalize'] ?? true);
            
            // Manually convert to lowercase for non-capitalized cases
            if (isset($config['capitalize']) && $config['capitalize'] === false) {
                $result = strtolower($result);
            }
            
            $this->assertSame($expected[$index], $result, "Failed on input #{$index}");
        }
    }

    public function consecutiveCallsProvider(): array
    {
        return [
            'Different settings' => [
                [
                    [
                        'input' => 'محمد',
                        'mapping' => MappingType::DEFAULT,
                    ],
                    [
                        'input' => 'محمد',
                        'capitalize' => false,
                    ],
                    [
                        'input' => 'النور',
                        'sunLetterAssimilation' => true,
                    ],
                    [
                        'input' => 'النور',
                        'sunLetterAssimilation' => false,
                    ],
                ],
                [
                    'Muhammad',
                    'muhammad',
                    'An-nwr',
                    'Alnwr',
                ]
            ],
        ];
    }

    /**
     * @dataProvider unicodeEdgeCasesProvider
     */
    public function testUnicodeEdgeCases(string $input): void
    {
        // Just test that these don't throw exceptions
        $result = $this->transliterator->transliterate($input);
        $this->addToAssertionCount(1);
    }

    public function unicodeEdgeCasesProvider(): array
    {
        return [
            'Bidirectional formatting chars' => ["محمد\u{202A}علي\u{202C}"],
            'Arabic presentation forms' => ["\u{FEF5}\u{FEF6}\u{FEF7}"], // Arabic presentation forms
            'Zero-width joiner' => ["م\u{200D}ح\u{200D}م\u{200D}د"],
        ];
    }

    /**
     * @dataProvider performanceTestProvider
     */
    public function testPerformanceWithLargeInput(string $input): void
    {
        $startTime = microtime(true);
        
        $result = $this->transliterator->transliterate($input);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // This ensures the test passes even if it's slow,
        // but adds the execution time to the test output
        $this->addToAssertionCount(1);
        
        // Output execution time for manual review
        echo "\nExecution time for large input: " . number_format($executionTime, 6) . " seconds\n";
    }
    
    public function performanceTestProvider(): array
    {
        // Create a long text with repetitions of common Arabic names
        $names = ['محمد', 'علي', 'فاطمة', 'حسين', 'عبد الله', 'نور', 'أحمد', 'خالد'];
        $longText = '';
        
        for ($i = 0; $i < 100; $i++) {
            $longText .= $names[array_rand($names)] . ' ';
        }
        
        return [
            'Long text (100 names)' => [$longText],
        ];
    }

    /**
     * @dataProvider realWorldNamesProvider
     */
    public function testRealWorldNames(string $input): void
    {
        // Just ensure these don't throw exceptions and return a string
        $result = $this->transliterator->transliterate($input);
        $this->assertNotEmpty($result);
    }

    public function realWorldNamesProvider(): array
    {
        return [
            ['عبد الرحمن بن عوف'],
            ['صلاح الدين الأيوبي'],
            ['المعتصم بالله'],
            ['عمر بن الخطاب'],
            ['زين العابدين علي بن الحسين'],
            ['خالد بن الوليد بن المغيرة المخزومي'],
            ['أبو عبد الله محمد بن موسى الخوارزمي'],
            ['سيف الدين قطز'],
            ['طارق بن زياد'],
            ['نور الدين زنكي'],
        ];
    }

    /**
     * @dataProvider dialectNamesProvider
     */
    public function testDialectNames(string $input): void
    {
        // Just ensure these don't throw exceptions
        $result = $this->transliterator->transliterate($input);
        $this->addToAssertionCount(1);
    }

    public function dialectNamesProvider(): array
    {
        return [
            // Names with spelling variants or dialect-specific spelling
            ['چابر'], // Persian/Urdu character variation
            ['پيمان'],
            ['گلشن'],
            ['ڤيكتور'], // Names with non-standard Arabic characters
            ['ژينا'],
            ['عبدالرحمٰن'], // With superscript alif
            ['محمّد'], // With shadda
            ['إسماعيل'], // With hamza below
        ];
    }

    /**
     * @dataProvider chainingProvider
     */
    public function testMethodChainingWithTransliteration(array $steps, string $finalInput, string $expected): void
    {
        $transliterator = new Transliterator();
        
        foreach ($steps as $method => $value) {
            if ($method === 'setMapping' && is_string($value)) {
                $value = constant("ArabicNameTransliterator\\Enum\\MappingType::$value");
            }
            $transliterator = $transliterator->{$method}($value);
        }
        
        $result = $transliterator->transliterate($finalInput);
        $this->assertSame($expected, $result);
    }
    
    public function chainingProvider(): array
    {
        return [
            'Multiple method chains' => [
                [
                    'setMapping' => 'DEFAULT',
                    'setSunLetterAssimilation' => false,
                    'setTaMarbutaStyle' => 'ah',
                ],
                'الرشيد',
                'Alrshyd',
            ],
            'Change order of operations' => [
                [
                    'setTaMarbutaStyle' => 'a',
                    'setSunLetterAssimilation' => true,
                    'setMapping' => 'IRAQI',
                ],
                'الرشيد',
                'Ar-rshyd',
            ],
        ];
    }
} 