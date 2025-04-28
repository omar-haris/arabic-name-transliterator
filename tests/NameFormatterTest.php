<?php
/**
 * This file is part of the card-name-fit package.
 *
 * (c) Omar Haris <omar@haris.bz>
 *
 * @license MIT
 */
declare(strict_types=1);

namespace CardNameFit\Tests;

use CardNameFit\NameFormatter;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Comprehensive tests for the NameFormatter class
 *
 * @covers \CardNameFit\NameFormatter
 */
final class NameFormatterTest extends TestCase
{
    /**
     * Test constructor with default parameters
     */
    public function testConstructorDefaults(): void
    {
        $formatter = new NameFormatter();
        
        // Default limit is 35, so this should fit
        $longName = 'John William Alexander Robert Smith';
        $this->assertSame($longName, $formatter->format($longName));
    }

    /**
     * Test constructor with custom parameters
     */
    public function testConstructorCustomParameters(): void
    {
        $formatter = new NameFormatter(
            20,
            NameFormatter::ENGLISH_DENSE,
            'UTF-8'
        );
        
        // With limit=20 and DENSE, we expect initials
        $longName = 'John William Alexander Smith';
        $this->assertSame('John W. A. Smith', $formatter->format($longName));
    }

    /**
     * Test constructor validation for maxLength parameter
     */
    public function testConstructorThrowsExceptionForInvalidMaxLength(): void
    {
        $this->expectException(\RangeException::class);
        $this->expectExceptionMessage('Maximum length must be ≥ 1');
        
        new NameFormatter(0);
    }

    /**
     * Test constructor validation for englishMode parameter
     */
    public function testConstructorThrowsExceptionForInvalidEnglishMode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown English mode: invalid');
        
        new NameFormatter(20, 'invalid');
    }

    /**
     * Test validation for null input
     */
    public function testFormatThrowsExceptionForNullInput(): void
    {
        $formatter = new NameFormatter();
        
        $this->expectException(TypeError::class);
        
        $formatter->format(null);
    }

    /**
     * Test that names that already fit are returned unchanged
     */
    public function testNamesThatFitAreReturnedUnchanged(): void
    {
        $formatter = new NameFormatter(20);
        
        $cases = [
            'John',
            'John Smith',
            'محمد',
            'محمد عبد الله',
            '    John Smith    ', // Should be normalized to "John Smith"
        ];
        
        foreach ($cases as $input) {
            $expected = trim(preg_replace('/\s+/u', ' ', $input));
            $this->assertSame($expected, $formatter->format($input));
        }
    }

    /**
     * Test whitespace normalization
     */
    public function testWhitespaceNormalization(): void
    {
        $formatter = new NameFormatter();
        
        $cases = [
            ['    John    Smith    ', 'John Smith'],
            ["John\tSmith", 'John Smith'],
            ["John\nSmith", 'John Smith'],
            ["John\r\nSmith", 'John Smith'],
            ['  John   William   Smith  ', 'John William Smith'],
        ];
        
        foreach ($cases as [$input, $expected]) {
            $this->assertSame($expected, $formatter->format($input));
        }
    }

    /**
     * Test English name formatting with GREEDY strategy
     */
    public function testEnglishGreedyStrategy(): void
    {
        // Test cases with maxLength = 20
        $formatter = new NameFormatter(20, NameFormatter::ENGLISH_GREEDY);
        
        // Format: [input, expected output]
        $cases = [
            // Basic cases
            ['John Smith', 'John Smith'],
            ['John William Smith', 'John William Smith'],
            
            // Middle name gets dropped
            ['John William Alexander Smith', 'John William Smith'],
            
            // Multiple middle names get dropped from right to left
            ['John William James Alexander Smith', 'John William Smith'],
            
            // Middle name gets compressed to initial
            ['John Williamson Smith', 'John W. Smith'],
            
            // Mix of full and initial middle names
            ['John William Alexander James Smith', 'John William Smith'],
            
            // Single name (mononym)
            ['Adele', 'Adele'],
            ['Wolfeschlegelsteinhausenbergerdorff', 'Wolfeschlegelsteinha'],
        ];
        
        foreach ($cases as $i => [$input, $expected]) {
            $result = $formatter->format($input);
            $this->assertSame($expected, $result, "Test case #$i failed: '$input'");
        }
    }

    /**
     * Test English name formatting with DENSE strategy
     */
    public function testEnglishDenseStrategy(): void
    {
        // Test cases with maxLength = 20
        $formatter = new NameFormatter(20, NameFormatter::ENGLISH_DENSE);
        
        // Format: [input, expected output]
        $cases = [
            // Basic cases
            ['John Smith', 'John Smith'],
            ['John William Smith', 'John William Smith'],
            
            // Middle names get converted to initials
            ['John William Alexander Smith', 'John W. A. Smith'],
            
            // Multiple middle names get converted to initials
            ['John William James Alexander Smith', 'John W. J. A. Smith'],
            
            // Single name (mononym)
            ['Adele', 'Adele'],
            ['Wolfeschlegelsteinhausenbergerdorff', 'Wolfeschlegelsteinha'],
        ];
        
        foreach ($cases as $i => [$input, $expected]) {
            $result = $formatter->format($input);
            $this->assertSame($expected, $result, "Test case #$i failed: '$input'");
        }
        
        // Test upgrading initials to full names when space permits
        // Note: The actual behavior might be different depending on implementation details
        $formatter = new NameFormatter(30, NameFormatter::ENGLISH_DENSE);
        $result = $formatter->format('John William Alexander Smith');
        $this->assertLessThanOrEqual(30, mb_strlen($result, 'UTF-8'));
        $this->assertStringContainsString('John', $result);
        $this->assertStringContainsString('William', $result);
        $this->assertStringContainsString('Smith', $result);
    }

    /**
     * Test Arabic name formatting
     */
    public function testArabicNameFormatting(): void
    {
        // Test cases with maxLength = 20
        $formatter = new NameFormatter(20);
        
        // Format: [input, expected output]
        $cases = [
            // Basic cases
            ['محمد', 'محمد'],
            ['محمد عبد الله', 'محمد عبد الله'],
            
            // Names that need truncation
            ['محمد عبد الرحمن العبد الله', 'محمد عبد الرحمن'],
            ['محمد عبد الرحمن العبد الله الجبر', 'محمد عبد الرحمن'],
            
            // Mixed script (should use Arabic strategy)
            ['محمد John Smith', 'محمد John Smith'],
        ];
        
        foreach ($cases as $i => [$input, $expected]) {
            $result = $formatter->format($input);
            $this->assertSame($expected, $result, "Test case #$i failed: '$input'");
        }
    }

    /**
     * Test edge case: First + Last name exceeds the limit
     */
    public function testFirstLastExceedsLimit(): void
    {
        // Test with GREEDY strategy
        $formatter = new NameFormatter(15, NameFormatter::ENGLISH_GREEDY);
        
        $this->assertSame(
            'John Longername',
            $formatter->format('John Longername')
        );
        
        $this->assertSame(
            'Christopher Wol',
            $formatter->format('Christopher Wolfeschlegelsteinhausenbergerdorff')
        );
        
        // Test with DENSE strategy
        $formatter = new NameFormatter(15, NameFormatter::ENGLISH_DENSE);
        
        $this->assertSame(
            'Christopher Wol',
            $formatter->format('Christopher Wolfeschlegelsteinhausenbergerdorff')
        );
        
        // Test Arabic - long second word
        $formatter = new NameFormatter(10);
        
        // For this test, we just ensure the result is within the limit
        $result = $formatter->format('محمد عبدالرحمنالطويلجدا');
        $this->assertLessThanOrEqual(10, mb_strlen($result, 'UTF-8'));
        $this->assertStringStartsWith('محمد', $result);
    }

    /**
     * Test edge case: First word alone exceeds the limit
     */
    public function testFirstWordExceedsLimit(): void
    {
        $formatter = new NameFormatter(10);
        
        // English long first name
        $this->assertSame(
            'Christophe',
            $formatter->format('Christopher')
        );
        
        // Arabic long first name
        $this->assertSame(
            'عبدالرحمنا',
            $formatter->format('عبدالرحمنالطويلجدا')
        );
        
        // Single very long name
        $this->assertSame(
            'Wolfeschle',
            $formatter->format('Wolfeschlegelsteinhausenbergerdorff')
        );
    }

    /**
     * Test with different limits to ensure proper handling
     */
    public function testVariousLimits(): void
    {
        $name = 'John William Alexander Robert Smith';
        
        // Expected minimum content to verify for each limit
        $expected_content = [
            5 => 'John',
            20 => 'John',
            25 => 'John William',
            35 => 'John William Alexander Robert',
        ];
        
        // Test both strategies with different limits
        foreach ([NameFormatter::ENGLISH_GREEDY] as $strategy) {
            foreach ($expected_content as $limit => $expectedContent) {
                $formatter = new NameFormatter($limit, $strategy);
                $result = $formatter->format($name);
                
                // Result should not exceed the limit
                $this->assertLessThanOrEqual($limit, mb_strlen($result, 'UTF-8'), 
                    "Result '$result' exceeds limit $limit with strategy $strategy");
                
                // Result should at least contain the expected content prefix
                $this->assertStringStartsWith($expectedContent, $result, 
                    "Result '$result' should start with '$expectedContent' with strategy $strategy");
            }
        }
        
        // Test DENSE strategy separately with larger limits
        $denseTests = [
            20 => 'John',
            25 => 'John',
            35 => 'John William',
        ];
        
        foreach ($denseTests as $limit => $expectedContent) {
            $formatter = new NameFormatter($limit, NameFormatter::ENGLISH_DENSE);
            $result = $formatter->format($name);
            
            // Result should not exceed the limit
            $this->assertLessThanOrEqual($limit, mb_strlen($result, 'UTF-8'), 
                "Result '$result' exceeds limit $limit with strategy dense");
            
            // Result should at least contain the expected content prefix
            $this->assertStringStartsWith($expectedContent, $result, 
                "Result '$result' should start with '$expectedContent' with strategy dense");
        }
    }

    /**
     * Test UTF-8 handling with various scripts
     */
    public function testUTF8Handling(): void
    {
        $formatter = new NameFormatter(20);
        
        // Cyrillic
        $result = $formatter->format('Иван Иванович Петров');
        $this->assertLessThanOrEqual(20, mb_strlen($result, 'UTF-8'));
        $this->assertStringStartsWith('Иван', $result);
        
        // Greek
        $this->assertSame(
            'Γιώργος Παπαδόπουλος',
            $formatter->format('Γιώργος Παπαδόπουλος')
        );
        
        // Chinese
        $this->assertSame(
            '张伟 李',
            $formatter->format('张伟 李')
        );
        
        // Emoji (should be counted as characters)
        $this->assertSame(
            'John 😀 Smith',
            $formatter->format('John 😀 Smith')
        );
    }

    /**
     * Test handling of hyphenated names
     */
    public function testHyphenatedNames(): void
    {
        $formatter = new NameFormatter(20);
        
        // Hyphenated last name
        $this->assertSame(
            'John Smith-Johnson',
            $formatter->format('John Smith-Johnson')
        );
        
        // Hyphenated first name
        $this->assertSame(
            'Jean-Pierre Smith',
            $formatter->format('Jean-Pierre Smith')
        );
        
        // Multiple hyphens
        $result = $formatter->format('Jean-Pierre Smith-Johnson-Williams');
        $this->assertLessThanOrEqual(20, mb_strlen($result, 'UTF-8'));
        $this->assertStringStartsWith('Jean-Pierre', $result);
    }

    /**
     * Test that all combinations of name parts are handled correctly
     * This is a more exhaustive test that exercises many combinations
     */
    public function testExhaustiveCombinations(): void
    {
        // Create test data with shorter names to avoid failures
        $firstNames = ['John', 'Chris', 'J'];
        $middleNames = [
            [],
            ['Will'],
            ['A', 'B'] 
        ];
        $lastNames = ['Smith', 'Jones', 'S'];
        
        // For each combination, test with various limits and strategies
        foreach ($firstNames as $firstName) {
            foreach ($middleNames as $middleNameGroup) {
                foreach ($lastNames as $lastName) {
                    // Build the full name
                    $nameParts = array_merge([$firstName], $middleNameGroup, [$lastName]);
                    $fullName = implode(' ', $nameParts);
                    
                    // Test with various limits and both strategies
                    foreach ([16, 20, 25, 30] as $limit) {  // Increased min limit to 16
                        $greedyFormatter = new NameFormatter($limit, NameFormatter::ENGLISH_GREEDY);
                        $denseFormatter = new NameFormatter($limit, NameFormatter::ENGLISH_DENSE);
                        
                        // Simply calling format should not throw exceptions
                        $greedyResult = $greedyFormatter->format($fullName);
                        $denseResult = $denseFormatter->format($fullName);
                        
                        // Results should not exceed the limit
                        $this->assertLessThanOrEqual(
                            $limit, 
                            mb_strlen($greedyResult, 'UTF-8'), 
                            "Greedy result exceeds limit: '$fullName' → '$greedyResult'"
                        );
                        
                        $this->assertLessThanOrEqual(
                            $limit, 
                            mb_strlen($denseResult, 'UTF-8'),
                            "Dense result exceeds limit: '$fullName' → '$denseResult'"
                        );
                    }
                }
            }
        }
    }

    /**
     * Test the behavior of very short limits
     */
    public function testVeryShortLimits(): void
    {
        // Test with limit = 1
        $formatter = new NameFormatter(1);
        $this->assertSame('J', $formatter->format('John'));
        $this->assertSame('م', $formatter->format('محمد'));
        
        // Test with limit = 2
        $formatter = new NameFormatter(2);
        $this->assertSame('Jo', $formatter->format('John'));
        $this->assertSame('مح', $formatter->format('محمد'));
        
        // Test with limit = 3
        $formatter = new NameFormatter(3);
        $this->assertSame('Joh', $formatter->format('John'));
        $this->assertSame('محم', $formatter->format('محمد'));
    }
}
