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

namespace ArabicNameTransliterator\Mapping;

/**
 * Iraqi-specific transliteration mapping optimized for Iraqi Arabic names.
 *
 * This mapping is tailored for Iraqi names, with a comprehensive list of:
 * - Common Iraqi first names (male and female)
 * - Special name components (like "Abd", "Al", etc.)
 * - Compound names (especially "Abd + X" forms)
 * - Multiple spelling variants for the same name
 *
 * The character mapping follows standard Iraqi transliteration conventions
 * used in official documents and international settings.
 *
 * Key characteristics:
 * - Uses "Muhammad" rather than "Mohammed" or "Mohamed"
 * - Preserves compound structures like "Abd Al-Rahman" (rather than "Abdelrahman")
 * - Maps ج to "j" rather than "g" (as in Egyptian dialect)
 * - Maps ث to "th" rather than "s" or "t"
 *
 * @author Omar Haris <omar@haris.bz>
 */
class IraqMapping extends BaseMapping
{
    /**
     * Returns a comprehensive dictionary of Iraqi names and name components
     * with their standard English transliterations.
     *
     * This dictionary includes:
     * - Common male and female Iraqi first names
     * - Name components like "Abd", "Al", "Bin"
     * - Compound forms like "Abdullah", "Abd Al-Rahman"
     * - Common family name components
     * - Multiple spelling variants (e.g., different diacritic patterns)
     *
     * Entries are carefully curated to follow standard Iraqi transliteration
     * conventions used in passports, official documents, and international settings.
     *
     * @return array<string, string> Mapping of Arabic names to English transliterations
     */
    public function getFullWordMap(): array
    {
        /*
         * Expanded dictionary for Iraqi usage:
         * (includes single names and common compound name segments)
         *
         * Note that keys must match the exact Arabic spelling
         * you want to catch. Variations like "عبدالله" vs. "عبد الله"
         * should each have their own entry if needed.
         */
        return [
            // Core name segments
            'عبد'         => 'Abd',
            'آل'          => 'Al',
            'بن'          => 'Bin',
            'ابو'         => 'Abu',
            'ابن'         => 'Ibn',

            // Frequently used words/names
            'نور'         => 'Noor',
            'محمد'        => 'Muhammad',
            'فاطمة'       => 'Fatimah',
            'علي'         => 'Ali',
            'ابراهيم'     => 'Ibrahim',
            'يحيى'        => 'Yahya',
            'حسين'        => 'Hussein',
            'حسن'         => 'Hassan',
            'سلمان'       => 'Salman',
            'جعفر'        => 'Jaafar',
            'كاظم'        => 'Kadhim',
            'رعد'         => 'Raad',
            'عمار'        => 'Ammar',
            'صباح'        => 'Sabah',
            'صادق'        => 'Sadiq',
            'زهرة'        => 'Zahra',
            'زينب'        => 'Zainab',
            'سجاد'        => 'Sajjad',
            'الرحمن'      => 'Al-Rahman',
            'العظيم'      => 'Al-Azeem',
            'الزهرا'      => 'Al-Zahraa',
            'احمد'        =>  'Ahmad',

            // Compound "Abd + X" forms
            'عبدالله'     => 'Abdullah',
            'عبد الله'    => 'Abd Allah',
            'عبد القادر'  => 'Abd Al-Qader',
            'عبد الوهاب'  => 'Abd Al-Wahab',
            'عبد المجيد'  => 'Abd Al-Majeed',
            'عبد المحسن'  => 'Abd Al-Muhsin',
            'عبد الرزاق'  => 'Abd Al-Razzaq',
            'عبد الستار'  => 'Abd Al-Sattar',
            'عبد الجبار'  => 'Abd Al-Jabbar',
            'عبد السلام'  => 'Abd Al-Salam',
            'عبد الرحمن'  => 'Abd Al-Rahman',

            // Other common male first names
            'حيدر'        => 'Haydar',
            'محمود'       => 'Mahmoud',
            'قصي'         => 'Qusay',
            'قيس'         => 'Qais',
            'طه'          => 'Taha',
            'ظافر'        => 'Dhafir',
            'عدنان'       => 'Adnan',
            'رائد'        => 'Raed',
            'رياض'        => 'Riyad',
            'فاضل'        => 'Fadil',
            'مهند'        => 'Mohannad',
            'مثنى'        => 'Muthanna',
            'مصطفى'       => 'Mustafa',
            'عمر'         => 'Omar',
            'يوسف'        => 'Yusuf',
            'وليد'        => 'Walid',
            'زياد'        => 'Ziad',
            'نزار'        => 'Nizar',
            'أسامة'       => 'Osama',
            'سعد'         => 'Saad',
            'سعدون'       => 'Saadun',
            'سامر'        => 'Samer',
            'مهدي'        => 'Mahdi',
            'جواد'        => 'Jawad',
            'حمد'         => 'Hamad',
            'حمدي'        => 'Hamdi',
            'بلال'        => 'Bilal',
            'هيثم'        => 'Haitham',
            'ثامر'        => 'Thamer',
            'طالب'        => 'Talib',
            'موسى'        => 'Musa',
            'أنس'         => 'Anas',
            'عامر'        => 'Amer',
            'مالك'        => 'Malik',
            'باسم'        => 'Basim',
            'عباس'        => 'Abbas',
            'سيف'         => 'Saif',
            'حارب'        => 'Harib',
            'معاذ'        => 'Muadh',
            'معن'         => 'Maan',
            'مأمون'       => 'Maamoon',
            'أحمد'        => 'Ahmed',
            'إبراهيم'     => 'Ibrahim',
            'مهنى'        => 'Muhanna',
            'رسول'        => 'Rasool',
            'وسام'        => 'Wesam',
            'ديار'        => 'Diyar',
            'غالب'        => 'Ghalib',
            'منذر'        => 'Munther',
            'عبيدة'       => 'Ubaidah',

            // Common female first names
            'مريم'        => 'Maryam',
            'بتول'        => 'Batool',
            'شيماء'       => 'Shaima',
            'رقيه'        => 'Ruqayya',
            'رقية'        => 'Ruqayya',
            'رقيّة'       => 'Ruqayya',
            'سمر'         => 'Samar',
            'جنان'        => 'Jinan',
            'فداء'        => 'Fida',
            'وفاء'        => 'Wafaa',
            'كوثر'        => 'Kawthar',
            'لبنى'        => 'Lubna',
            'ضحى'         => 'Doha',
            'هدى'         => 'Huda',
            'هدية'        => 'Hadiya',
            'سناء'        => 'Sana',
            'سلمى'        => 'Salma',
            'حنان'        => 'Hanan',
            'رغد'         => 'Raghad',
            'رغدة'        => 'Raghda',
            'سارة'        => 'Sarah',
            'سهام'        => 'Siham',
            'نادية'       => 'Nadia',
            'بشرى'        => 'Bushra',
            'حليمة'       => 'Halima',
            'حميدة'       => 'Hamida',
            'عائشة'       => 'Aisha',
            'خلود'        => 'Kholoud',
            'حلا'         => 'Hala',
            'سندس'        => 'Sondos',
            'مي'          => 'Mai',
            'مها'         => 'Maha',
            'ميادة'       => 'Mayada',
            'هديل'        => 'Hadeel',
            'هبة'         => 'Heba',
            'آمنة'        => 'Amina',
            'أماني'       => 'Amani',
            'أميرة'       => 'Amira',
            'صبا'         => 'Saba',
            'فرح'         => 'Farah',
            'رنيم'        => 'Raneem',
            'بلقيس'       => 'Balqees',
            'نوران'       => 'Nouran',

            // Misc or compound
            'نور الهدى'    => 'Noor Al-Huda',
            'ام كلثوم'     => 'Umm Kulthum',
            'أم كلثوم'     => 'Umm Kulthum',
            'سعدي'         => 'Saadi',
            'سهيل'         => 'Suhail',
            'ناجي'         => 'Naji',
            'نجم'          => 'Najm',
            'عقيل'         => 'Aqeel',
            'عقيلة'        => 'Aqeelah',
            'رضا'          => 'Rida',
            'مروة'         => 'Marwa',
            'وعد'          => 'Waad',
            'كفاح'         => 'Kifah',
            'نرمين'        => 'Narmin',
            'عفراء'        => 'Afraa',
            'حليمه'        => 'Halima',
            'حميده'        => 'Hamida',
            'سراچ'         => 'Siraj',
            'سراج'         => 'Siraj',
            'جلال'         => 'Jalal',
            'سعود'         => 'Saud',
            'حنين'         => 'Haneen',
            'سيما'         => 'Sima',
            'سماح'         => 'Samah',
            'إسراء'        => 'Israa',
            'هند'          => 'Hind',
            'زين'          => 'Zain',
            'زينة'         => 'Zaina',
            'زينه'         => 'Zaina',
            'حسين علي'     => 'Hussein Ali',
            'محمد علي'     => 'Muhammad Ali',
            'بنت الهدى'     => 'Bint Al-Huda',
            'عبد الزهرة'    => 'Abd Al-Zahra',
            'عبد الأمير'    => 'Abd Al-Amir',
            'عبد الصاحب'    => 'Abd Al-Sahib',
            'عبد الجليل'    => 'Abd Al-Jalil',
            'عبد الرضا'     => 'Abd Al-Ridha',
            'عبد الشهيد'    => 'Abd Al-Shahid',
            'جعفر الصادق'   => 'Jaafar Al-Sadiq',
            'عز الدين'      => 'Ezz Al-Din',
            'نور الدين'     => 'Noor Al-Din',
            'شمس الدين'     => 'Shams Al-Din',
            'صدر الدين'     => 'Sadr Al-Din',
            'ركن الدين'     => 'Rukn Al-Din',
            'عبد الخالق'    => 'Abd Al-Khaliq',
            'عبد الامام'    => 'Abd Al-Imam',
            'عبد السميع'    => 'Abd Al-Samee',
            'عبد الوارث'    => 'Abd Al-Warith',
            'عبد العظيم'     => 'Abd Al-Azeem',
            'عبد العال'     => 'Abd Al-Aal',
            'عبد القهار'    => 'Abd Al-Qahhar',
            'عبد الرب'      => 'Abd Al-Rabb',
            'عبد الباسط'    => 'Abd Al-Basit',
            'عبد الهادي'    => 'Abd Al-Hadi',
            'عبد الودود'    => 'Abd Al-Wadood',
            'محمد مهدي'     => 'Muhammad Mahdi',

            // Iraqi family names
            'البصري'          => 'Al-Basri',
            'النجفي'          => 'Al-Najafi',
            'الموصلي'         => 'Al-Mosuli',
            'الكاظمي'         => 'Al-Kadhimi',
            'السماوي'         => 'Al-Samawi',
            'الدليمي'         => 'Al-Dulaimi',
            'الربيعي'         => 'Al-Rubaie',
            'الطائي'          => 'Al-Taee',
            'الشيباني'        => 'Al-Shaibani',
            'الحسيني'         => 'Al-Husseini',
            'المرعبي'         => 'Al-Marrabi',
            'البغدادي'        => 'Al-Baghdadi',
            'الموسوي'         => 'Al-Mousawi',
            'الخزاعي'         => 'Al-Khuzaie',
        ];
    }

    /**
     * Returns the mapping of Arabic letters to their English equivalents
     * following Iraqi transliteration conventions.
     *
     * This mapping includes:
     * - Basic Arabic letters with their standard transliterations
     * - Various forms of hamza with appropriate transliterations
     * - Diacritics (harakat) mapped to empty strings
     * - Special characters and punctuation
     *
     * This serves as a fallback for words not found in the full-word
     * dictionary and follows conventions commonly used in Iraqi passports
     * and official documents.
     *
     * @return array<string, string> Letter-by-letter mapping for Iraqi transliteration
     */
    public function getLetterMap(): array
    {
        // Basic Arabic letters and common diacritics (diacritics mapped to empty).
        return [
            // Hamzas and variants
            'أ' => 'a', 'ا' => 'a', 'إ' => 'i', 'آ' => 'a',
            'ء' => '',  'ؤ' => 'u', 'ئ' => 'i',

            // Consonants
            'ب' => 'b', 'ت' => 't', 'ث' => 'th',
            'ج' => 'j', 'ح' => 'h', 'خ' => 'kh',
            'د' => 'd', 'ذ' => 'dh', 'ر' => 'r',
            'ز' => 'z', 'س' => 's', 'ش' => 'sh',
            'ص' => 's', 'ض' => 'dh','ط' => 't',
            'ظ' => 'dh','ع' => 'a', 'غ' => 'gh',
            'ف' => 'f', 'ق' => 'q', 'ك' => 'k',
            'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ي' => 'y',

            // Extra Persian letters (used in some Iraqi contexts)
            'چ' => 'ch', // e.g., "چاي" → "chai"
            'پ' => 'p',
            'ڤ' => 'v',
            'گ' => 'g',
            'ژ' => 'zh',

            // Ta marbuta (we will handle final form in code)
            'ة' => 'h', // We'll refine final forms in code logic


            // Diacritics (mapped to empty for simpler transliteration)
            'َ' => '', 'ً' => '', 'ُ' => '', 'ٌ' => '',
            'ِ' => '', 'ٍ' => '', 'ّ' => '', 'ْ' => '',
            'ـ' => '', // Tatweel (stretch)

            // Arabic numerals
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3',
            '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7',
            '٨' => '8', '٩' => '9',

            // Common punctuation & extra symbols
            ',' => ',',  '.' => '.',  '!' => '!',  '?' => '?',
            '،' => ',',  '؛' => ';',  '؟' => '?',
            '(' => '(',  ')' => ')',  '"' => '"',
            '«' => '"',  '»' => '"',  '[' => '[',  ']' => ']',
            '{' => '{',  '}' => '}',  '…' => '...'
        ];
    }
}
