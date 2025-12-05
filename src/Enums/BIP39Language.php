<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Enums;

/**
 * Supported BIP39 mnemonic languages.
 *
 * Each language corresponds to a wordlist file in the BIP39 specification.
 * The wordlists contain 2048 unique words for generating mnemonics.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum BIP39Language: string
{
    case ChineseSimplified = 'chinese_simplified';

    case ChineseTraditional = 'chinese_traditional';

    case Czech = 'czech';

    case English = 'english';

    case French = 'french';

    case Italian = 'italian';

    case Japanese = 'japanese';

    case Korean = 'korean';

    case Spanish = 'spanish';

    /**
     * Resolve the filesystem path to the language-specific wordlist file.
     *
     * @return string Absolute path to the wordlist text file containing 2048 words
     */
    public function getFilePath(): string
    {
        return __DIR__.'/../WordLists/BIP39/'.$this->value.'.txt';
    }

    /**
     * Get the culturally appropriate word separator for the language.
     *
     * Returns the Unicode ideographic space (U+3000) for Japanese mnemonics
     * to match cultural conventions, and standard ASCII space for all other
     * languages following BIP39 specification guidelines.
     *
     * @return string Single-character separator between mnemonic words
     */
    public function getDefaultSeparator(): string
    {
        return match ($this) {
            self::Japanese => "\u{3000}",
            default => ' ',
        };
    }
}
