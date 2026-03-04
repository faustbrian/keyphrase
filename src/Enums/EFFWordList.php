<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Enums;

/**
 * EFF Diceware wordlists for generating passphrases.
 *
 * The Electronic Frontier Foundation provides several wordlists
 * optimized for different use cases.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum EFFWordList: string
{
    /**
     * Long list: 7,776 words (5 dice rolls per word).
     * Best entropy, longer words.
     */
    case Large = 'eff_large_wordlist';

    /**
     * Short list 1: 1,296 words (4 dice rolls per word).
     * Shorter words, easier to type.
     */
    case Short = 'eff_short_wordlist_1';

    /**
     * Short list 2: 1,296 words with unique 3-character prefixes.
     * Autocomplete-friendly.
     */
    case UniquePrefix = 'eff_short_wordlist_2_0';

    /**
     * Resolve the filesystem path to the EFF wordlist file.
     *
     * @return string Absolute path to the wordlist text file
     */
    public function getFilePath(): string
    {
        return __DIR__.'/../WordLists/EFF/'.$this->value.'.txt';
    }

    /**
     * Get the total number of words available in the wordlist.
     *
     * Returns 7,776 words for the large list (requiring 5 dice rolls per word)
     * or 1,296 words for the short lists (requiring 4 dice rolls per word).
     *
     * @return int The wordlist size, corresponding to dice roll combinations
     */
    public function getWordCount(): int
    {
        return match ($this) {
            self::Large => 7_776,
            self::Short, self::UniquePrefix => 1_296,
        };
    }

    /**
     * Calculate the entropy bits contributed by selecting one word.
     *
     * Large list provides 12.925 bits per word (log2(7776)), while short
     * lists provide 10.34 bits per word (log2(1296)). Higher entropy values
     * indicate stronger passphrases for the same word count.
     *
     * @return float Entropy measured in bits for a single word selection
     */
    public function getEntropyPerWord(): float
    {
        return match ($this) {
            self::Large => 12.925,
            self::Short, self::UniquePrefix => 10.34,
        };
    }
}
