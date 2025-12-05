<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Generators;

use Cline\Keyphrase\Contracts\Generator;
use Cline\Keyphrase\Enums\EFFWordList;
use Cline\Keyphrase\Exceptions\WordCountTooFewException;
use Cline\Keyphrase\Support\SecureRandom;
use Cline\Keyphrase\Support\WordListLoader;

use function array_map;
use function array_splice;
use function count;
use function implode;
use function log;
use function mb_strtolower;
use function mb_strtoupper;
use function ucfirst;

/**
 * Fluent passphrase generator using EFF diceware wordlists.
 *
 * Generates cryptographically secure passphrases from EFF wordlists with configurable
 * word count, separators, and case transformations. Uses immutable fluent interface
 * pattern for configuration.
 *
 * ```php
 * $passphrase = Keyphrase::passphrase()
 *     ->words(6)
 *     ->separator('-')
 *     ->titleCase()
 *     ->generate();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PassphraseGenerator implements Generator
{
    /**
     * EFF wordlist to use for word selection.
     */
    private EFFWordList $wordList = EFFWordList::Large;

    /**
     * Number of words to include in the passphrase.
     */
    private int $wordCount = 6;

    /**
     * Character(s) to use between words.
     */
    private string $separator = '-';

    /**
     * Whether to capitalize the first letter of each word.
     */
    private bool $titleCase = false;

    /**
     * Whether to make all words uppercase.
     */
    private bool $uppercase = false;

    /**
     * Whether to make all words lowercase.
     */
    private bool $lowercase = false;

    /**
     * Whether to include a random number at a random position.
     */
    private bool $includeNumber = false;

    /**
     * Cached wordlist to avoid repeated file I/O.
     *
     * @var null|array<int, string>
     */
    private ?array $cachedWords = null;

    /**
     * Create a new passphrase generator instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Use the large EFF wordlist (7,776 words, best entropy).
     *
     * Provides the highest entropy with ~12.9 bits per word. Recommended for
     * maximum security applications.
     */
    public function large(): self
    {
        $clone = clone $this;
        $clone->wordList = EFFWordList::Large;
        $clone->cachedWords = null;

        return $clone;
    }

    /**
     * Use the short EFF wordlist (1,296 words, easier to type).
     *
     * Provides ~10.3 bits per word. Words are shorter and easier to type,
     * but require more words for equivalent entropy.
     */
    public function short(): self
    {
        $clone = clone $this;
        $clone->wordList = EFFWordList::Short;
        $clone->cachedWords = null;

        return $clone;
    }

    /**
     * Use the unique prefix EFF wordlist (autocomplete-friendly).
     *
     * Words have unique 4-character prefixes, enabling autocomplete in interfaces.
     * Provides same entropy as large list but optimized for user input scenarios.
     */
    public function uniquePrefix(): self
    {
        $clone = clone $this;
        $clone->wordList = EFFWordList::UniquePrefix;
        $clone->cachedWords = null;

        return $clone;
    }

    /**
     * Use a specific EFF wordlist.
     *
     * @param EFFWordList $wordList The wordlist to use for word selection
     */
    public function useWordList(EFFWordList $wordList): self
    {
        $clone = clone $this;
        $clone->wordList = $wordList;
        $clone->cachedWords = null;

        return $clone;
    }

    /**
     * Set the number of words in the passphrase.
     *
     * More words provide higher entropy. For the large wordlist, 6 words provides
     * ~77 bits of entropy, while 8 words provides ~103 bits.
     *
     * @param int $count Number of words (minimum 1)
     *
     * @throws WordCountTooFewException When count is less than 1
     */
    public function words(int $count): self
    {
        if ($count < 1) {
            throw WordCountTooFewException::forCount($count, 1);
        }

        $clone = clone $this;
        $clone->wordCount = $count;

        return $clone;
    }

    /**
     * Set the separator between words.
     *
     * @param string $separator Character(s) to use between words
     */
    public function separator(string $separator): self
    {
        $clone = clone $this;
        $clone->separator = $separator;

        return $clone;
    }

    /**
     * Use space as separator.
     */
    public function withSpaces(): self
    {
        return $this->separator(' ');
    }

    /**
     * Use no separator.
     */
    public function noSeparator(): self
    {
        return $this->separator('');
    }

    /**
     * Capitalize the first letter of each word.
     *
     * Mutually exclusive with uppercase() and lowercase(). When enabled,
     * disables other case transformations.
     *
     * @param bool $enable Whether to enable title case
     */
    public function titleCase(bool $enable = true): self
    {
        $clone = clone $this;
        $clone->titleCase = $enable;

        if ($enable) {
            $clone->uppercase = false;
            $clone->lowercase = false;
        }

        return $clone;
    }

    /**
     * Make all words uppercase.
     *
     * Mutually exclusive with titleCase() and lowercase(). When enabled,
     * disables other case transformations.
     *
     * @param bool $enable Whether to enable uppercase
     */
    public function uppercase(bool $enable = true): self
    {
        $clone = clone $this;
        $clone->uppercase = $enable;

        if ($enable) {
            $clone->titleCase = false;
            $clone->lowercase = false;
        }

        return $clone;
    }

    /**
     * Make all words lowercase.
     *
     * Mutually exclusive with titleCase() and uppercase(). When enabled,
     * disables other case transformations.
     *
     * @param bool $enable Whether to enable lowercase
     */
    public function lowercase(bool $enable = true): self
    {
        $clone = clone $this;
        $clone->lowercase = $enable;

        if ($enable) {
            $clone->titleCase = false;
            $clone->uppercase = false;
        }

        return $clone;
    }

    /**
     * Include a random number at a random position.
     *
     * Adds a number between 0-99 at a random position in the passphrase,
     * increasing entropy by approximately 9.6 bits.
     *
     * @param bool $enable Whether to include a number
     */
    public function includeNumber(bool $enable = true): self
    {
        $clone = clone $this;
        $clone->includeNumber = $enable;

        return $clone;
    }

    /**
     * Generate the passphrase.
     *
     * Selects random words from the configured wordlist using cryptographically
     * secure random number generation, then applies case transformations and
     * optional number insertion.
     *
     * @return string The generated passphrase
     */
    public function generate(): string
    {
        $words = $this->getWordList();
        $selectedWords = SecureRandom::elements($words, $this->wordCount);

        if ($this->titleCase) {
            $selectedWords = array_map(
                ucfirst(...),
                $selectedWords,
            );
        } elseif ($this->uppercase) {
            $selectedWords = array_map(
                mb_strtoupper(...),
                $selectedWords,
            );
        } elseif ($this->lowercase) {
            $selectedWords = array_map(
                mb_strtolower(...),
                $selectedWords,
            );
        }

        if ($this->includeNumber) {
            $number = (string) SecureRandom::int(0, 99);
            $position = SecureRandom::int(0, count($selectedWords));
            array_splice($selectedWords, $position, 0, [$number]);
        }

        return implode($this->separator, $selectedWords);
    }

    /**
     * Generate multiple passphrases.
     *
     * Each passphrase is independently generated with cryptographically
     * secure randomness.
     *
     * @param  int                $count Number of passphrases to generate
     * @return array<int, string>
     */
    public function generateMany(int $count): array
    {
        $passphrases = [];

        for ($i = 0; $i < $count; ++$i) {
            $passphrases[] = $this->generate();
        }

        return $passphrases;
    }

    /**
     * Calculate the entropy in bits.
     *
     * Calculates entropy based on wordlist size and word count using the formula:
     * entropy = wordCount * log2(wordlistSize). Includes additional entropy from
     * optional number insertion.
     *
     * @return float Entropy in bits
     */
    public function entropy(): float
    {
        $wordListSize = $this->wordList->getWordCount();

        $entropy = $this->wordCount * log($wordListSize, 2);

        if ($this->includeNumber) {
            // Add entropy for the number (0-99) and position
            $entropy += log(100, 2) + log($this->wordCount + 1, 2);
        }

        return $entropy;
    }

    /**
     * Get the loaded wordlist.
     *
     * Loads the wordlist from disk on first access and caches it for subsequent calls
     * to avoid repeated file I/O operations.
     *
     * @return array<int, string>
     */
    private function getWordList(): array
    {
        if ($this->cachedWords === null) {
            $this->cachedWords = WordListLoader::load($this->wordList->getFilePath());
        }

        return $this->cachedWords;
    }
}
