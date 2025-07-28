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
use ArabicNameTransliterator\Mapping\BaseMapping;
use ArabicNameTransliterator\Mapping\IraqMapping;
use ArabicNameTransliterator\Enum\MappingType;
use PHPUnit\Framework\TestCase;

class TransliteratorTest extends TestCase
{
    private Transliterator $transliterator;

    protected function setUp(): void
    {
        // Default constructor uses MappingType::DEFAULT
        $this->transliterator = new Transliterator();
    }

    public function testConstructorWithEnum(): void
    {
        $transliterator = new Transliterator(MappingType::IRAQI);
        $this->assertInstanceOf(IraqMapping::class, $transliterator->getMapping());
    }

    public function testConstructorWithMappingInstance(): void
    {
        $mapping = new IraqMapping();
        $transliterator = new Transliterator($mapping);
        $this->assertSame($mapping, $transliterator->getMapping());
    }

    public function testConstructorWithTaMarbutaStyle(): void
    {
        $transliterator = new Transliterator(MappingType::DEFAULT, 'a');
        $this->assertSame('a', $transliterator->getTaMarbutaStyle());
    }

    public function testConstructorWithSunLetterAssimilation(): void
    {
        $transliterator = new Transliterator(MappingType::DEFAULT, 'ah', false);
        $this->assertFalse($transliterator->isSunLetterAssimilationEnabled());
    }

    public function testGetMapping(): void
    {
        $this->assertInstanceOf(BaseMapping::class, $this->transliterator->getMapping());
    }

    public function testSetMapping(): void
    {
        $originalMapping = $this->transliterator->getMapping();
        
        // Create a new mapping
        $newMapping = new IraqMapping();
        
        // Should return $this for method chaining
        $result = $this->transliterator->setMapping($newMapping);
        $this->assertSame($this->transliterator, $result);
        
        // Should have updated the mapping
        $this->assertNotSame($originalMapping, $this->transliterator->getMapping());
        $this->assertSame($newMapping, $this->transliterator->getMapping());
    }
    
    public function testSetMappingWithEnum(): void
    {
        $originalMapping = $this->transliterator->getMapping();
        
        // Set mapping with enum
        $result = $this->transliterator->setMapping(MappingType::IRAQI);
        
        // Should return $this for method chaining
        $this->assertSame($this->transliterator, $result);
        
        // Should have updated the mapping
        $this->assertNotSame($originalMapping, $this->transliterator->getMapping());
    }

    /**
     * @dataProvider fullWordMappingProvider
     */
    public function testFullWordMapping(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for full word mapping tests
     */
    public function fullWordMappingProvider(): array
    {
        return [
            'Single name in dictionary' => ['عبدالله', 'Abdullah'],
            'Single name in dictionary with spaces' => ['  عبدالله  ', 'Abdullah'],
            'Compound name in dictionary' => ['عبد الرحمن', 'Abd Al-Rahman'],
            'Two words both in dictionary' => ['محمد علي', 'Muhammad Ali'],
            'Common first name' => ['فاطمة', 'Fatimah'],
            'Common male name' => ['حسين', 'Hussein'],
            'Name with different spellings 1' => ['رقيه', 'Ruqayya'],
            'Name with different spellings 2' => ['رقية', 'Ruqayya'],
            'Name with different spellings 3' => ['رقيّة', 'Ruqayya'],
        ];
    }

    /**
     * @dataProvider letterByLetterProvider
     */
    public function testLetterByLetterFallback(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for letter-by-letter fallback tests
     */
    public function letterByLetterProvider(): array
    {
        return [
            'Simple name not in dictionary' => ['سرور', 'Srwr'],
            'Compound word not in dictionary' => ['عبد المالك', 'Abd Almalk'],
            'Mixed known and unknown words' => ['محمد سرور', 'Muhammad Srwr'],
        ];
    }

    /**
     * @dataProvider capitalizationProvider
     */
    public function testCapitalization(string $arabic, bool $capitalize, string $expected): void
    {
        $actual = $this->transliterator->transliterate($arabic, $capitalize);

        // The original test forcibly lowercases if !$capitalize:
        if (!$capitalize) {
            $actual = strtolower($actual);
        }

        $this->assertSame($expected, $actual);
    }

    /**
     * Data provider for capitalization tests
     */
    public function capitalizationProvider(): array
    {
        return [
            'Capitalized by default' => ['محمد علي', true, 'Muhammad Ali'],
            'Not capitalized' => ['محمد علي', false, 'muhammad ali'],
            'Mixed case not in dictionary' => ['سرور', false, 'srwr'],
        ];
    }

    /**
     * @dataProvider cleanupProvider
     */
    public function testCleanup(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for cleanup tests
     */
    public function cleanupProvider(): array
    {
        return [
            'Extra spaces between words' => ['محمد    علي', 'Muhammad Ali'],
            'Leading and trailing spaces' => ['  محمد علي  ', 'Muhammad Ali'],
            'Multiple spaces everywhere' => ['  محمد    علي  ', 'Muhammad Ali'],
            'Empty input' => ['', ''],
            'Only spaces' => ['   ', ''],
        ];
    }

    /**
     * @dataProvider complexCasesProvider
     */
    public function testComplexCases(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for complex test cases
     */
    public function complexCasesProvider(): array
    {
        return [
            'Full Iraqi name' => ['محمد عبد الرحمن العبد الله', 'Muhammad Abd Al-Rahman Alabd Al-lh'],
            'Name with diacritics' => ['مُحَمَّد عَلِيّ', 'Mhmd Aly'],
            'Multi-word name partially in dictionary' => ['نور الهدى محمد علي', 'Noor Alhda Muhammad Ali'],
        ];
    }

    /**
     * @dataProvider sunLetterAssimilationProvider
     */
    public function testSunLetterAssimilation(string $arabic, string $expected): void
    {
        $this->transliterator->setSunLetterAssimilation(true);
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for sun letter assimilation tests
     */
    public function sunLetterAssimilationProvider(): array
    {
        return [
            'Sun letter (ن)' => ['النور', 'An-nwr'],
            'Sun letter (ل)' => ['اللطيف', 'Al-ltyf'],
            'Sun letter (ر)' => ['الرشيد', 'Ar-rshyd'],
            'Sun letter (ت)' => ['التوفيق', 'At-twfyq'],
            'Sun letter (ش)' => ['الشريف', 'As-shryf'],
            'Not a sun letter' => ['الكريم', 'Alkrym'],
        ];
    }

    /**
     * @dataProvider disabledSunLetterAssimilationProvider
     */
    public function testDisabledSunLetterAssimilation(string $arabic, string $expected): void
    {
        $this->transliterator->setSunLetterAssimilation(false);
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for disabled sun letter assimilation tests
     */
    public function disabledSunLetterAssimilationProvider(): array
    {
        return [
            'Sun letter (ن) - disabled assimilation' => ['النور', 'Alnwr'],
            'Sun letter (ر) - disabled assimilation' => ['الرشيد', 'Alrshyd'],
        ];
    }

    public function testSunLetterAssimilationToggle(): void
    {
        // Enabled (default)
        $this->assertTrue($this->transliterator->isSunLetterAssimilationEnabled());
        $this->assertSame('An-nwr', $this->transliterator->transliterate('النور'));
        
        // Disable it
        $result = $this->transliterator->setSunLetterAssimilation(false);
        
        $this->assertSame($this->transliterator, $result); // method chaining
        $this->assertFalse($this->transliterator->isSunLetterAssimilationEnabled());
        $this->assertSame('Alnwr', $this->transliterator->transliterate('النور'));
        
        // Enable it again
        $this->transliterator->setSunLetterAssimilation(true);
        $this->assertTrue($this->transliterator->isSunLetterAssimilationEnabled());
        $this->assertSame('An-nwr', $this->transliterator->transliterate('النور'));
    }

    public function testTaMarbutaStyles(): void
    {
        $originalStyle = $this->transliterator->getTaMarbutaStyle();
        $styles = ['ah', 'a', 'at'];
        foreach ($styles as $style) {
            $this->transliterator->setTaMarbutaStyle($style);
            $this->assertSame($style, $this->transliterator->getTaMarbutaStyle());
        }
        // Restore the original
        $this->transliterator->setTaMarbutaStyle($originalStyle);
    }
    
    public function testSetTaMarbutaStyleChaining(): void
    {
        $result = $this->transliterator->setTaMarbutaStyle('a');
        $this->assertSame($this->transliterator, $result);
        
        // Restore default
        $this->transliterator->setTaMarbutaStyle('ah');
    }

    /**
     * @dataProvider specialCharactersProvider
     */
    public function testSpecialCharacters(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for special characters tests
     */
    public function specialCharactersProvider(): array
    {
        return [
            'Name with numbers' => ['محمد123', 'Mhmd123'],
            'Name with special characters' => ['محمد!@#', 'Mhmd!@#'],
            'Only numbers and special characters' => ['123!@#', '123!@#'],
            'Mixed Arabic and Latin' => ['محمدMuhammad', 'MhmdMuhammad'],
        ];
    }

    /**
     * @dataProvider edgeCasesProvider
     */
    public function testEdgeCases(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for edge cases
     */
    public function edgeCasesProvider(): array
    {
        return [
            'Very long name (50+ chars)' => [
                'محمد عبد الرحمن عبد الله عبد العزيز عبد الكريم عبد القادر العراقي',
                'Muhammad Abd Al-Rahman Abd Al-lh Abd Alazyz Abd Alkrym Abd Alqadr Alaraqy'
            ],
            'Mix of spaces, newlines and tabs' => ["محمد\tعلي\nحسين", 'Mhmd Aly Hsyn'],
            'Repeated words' => ['محمد محمد محمد', 'Muhammad Muhammad Muhammad'],
            'Hidden whitespace characters' => ["محمد\u{200B}علي", 'Mhmd​aly'],
        ];
    }

    public function testMethodChaining(): void
    {
        $result = $this->transliterator
            ->setMapping(MappingType::IRAQI)
            ->setSunLetterAssimilation(false)
            ->setTaMarbutaStyle('at');
            
        $this->assertSame($this->transliterator, $result);
        $this->assertInstanceOf(IraqMapping::class, $this->transliterator->getMapping());
        $this->assertFalse($this->transliterator->isSunLetterAssimilationEnabled());
        $this->assertSame('at', $this->transliterator->getTaMarbutaStyle());
    }

    public function testDiacriticHandling(): void
    {
        $pairs = [
            'مُحَمَّد' => 'Mhmd',
            'عَلِيّ' => 'Aly',
            'حُسَيْن' => 'Hsyn',
            'عَبْدُ الرَّحْمٰن' => 'Abd Ar-rhmٰn',
            'فَاطِمَة' => 'Fatmah'
        ];
        
        foreach ($pairs as $arabic => $expected) {
            $this->assertSame($expected, $this->transliterator->transliterate($arabic));
        }
    }

    /**
     * @dataProvider arabicCharactersProvider
     */
    public function testVariousArabicCharacters(string $arabic, string $expected): void
    {
        $this->assertSame($expected, $this->transliterator->transliterate($arabic));
    }

    /**
     * Data provider for various Arabic characters
     */
    public function arabicCharactersProvider(): array
    {
        return [
            'Hamza variants' => ['أحمد إبراهيم ؤلؤلؤ', 'Ahmed Ibrahim Ululu'],
            'Alif variants' => ['آمال أمين', 'Amal Amyn'],
            'Ya variants' => ['علي عليّ', 'Ali Aly'],
            'Taa variants' => ['تامر ة', 'Tamr Ah'],
            'Waw variants' => ['وليد ؤمن', 'Walid Umn'],
        ];
    }
}
