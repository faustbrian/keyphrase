<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Generators;

use Cline\Keyphrase\Contracts\Generator;
use Cline\Keyphrase\Enums\BIP39Language;
use Cline\Keyphrase\Exceptions\WordCountNotInAllowedSetException;
use Cline\Keyphrase\Support\WordListLoader;

use const STR_PAD_LEFT;

use function array_map;
use function assert;
use function base_convert;
use function bin2hex;
use function bindec;
use function hash;
use function hex2bin;
use function implode;
use function in_array;
use function mb_str_pad;
use function mb_str_split;
use function mb_strlen;
use function ord;
use function random_bytes;

/**
 * BIP39 mnemonic phrase generator for cryptocurrency wallets.
 *
 * Generates cryptographically secure mnemonic phrases following the BIP39 standard.
 * Supports multiple languages and word counts (12, 15, 18, 21, 24 words).
 *
 * ```php
 * $mnemonic = Keyphrase::mnemonic()
 *     ->words(24)
 *     ->english()
 *     ->generate();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MnemonicGenerator implements Generator
{
    private const array ALLOWED_WORD_COUNTS = [12, 15, 18, 21, 24];

    private BIP39Language $language = BIP39Language::English;

    private int $wordCount = 12;

    private ?string $separator = null;

    /** @var null|array<int, string> */
    private ?array $cachedWords = null;

    /**
     * Create a new mnemonic generator with default configuration.
     *
     * Initializes with 12 words in English language.
     *
     * @return self A new generator instance
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Configure generator to use English wordlist.
     *
     * @return self Immutable clone with English language configured
     */
    public function english(): self
    {
        return $this->useLanguage(BIP39Language::English);
    }

    /**
     * Configure generator to use Spanish wordlist.
     *
     * @return self Immutable clone with Spanish language configured
     */
    public function spanish(): self
    {
        return $this->useLanguage(BIP39Language::Spanish);
    }

    /**
     * Configure generator to use French wordlist.
     *
     * @return self Immutable clone with French language configured
     */
    public function french(): self
    {
        return $this->useLanguage(BIP39Language::French);
    }

    /**
     * Configure generator to use Italian wordlist.
     *
     * @return self Immutable clone with Italian language configured
     */
    public function italian(): self
    {
        return $this->useLanguage(BIP39Language::Italian);
    }

    /**
     * Configure generator to use Japanese wordlist with ideographic space separator.
     *
     * @return self Immutable clone with Japanese language configured
     */
    public function japanese(): self
    {
        return $this->useLanguage(BIP39Language::Japanese);
    }

    /**
     * Configure generator to use Korean wordlist.
     *
     * @return self Immutable clone with Korean language configured
     */
    public function korean(): self
    {
        return $this->useLanguage(BIP39Language::Korean);
    }

    /**
     * Configure generator to use Czech wordlist.
     *
     * @return self Immutable clone with Czech language configured
     */
    public function czech(): self
    {
        return $this->useLanguage(BIP39Language::Czech);
    }

    /**
     * Configure generator to use Simplified Chinese wordlist.
     *
     * @return self Immutable clone with Simplified Chinese language configured
     */
    public function chineseSimplified(): self
    {
        return $this->useLanguage(BIP39Language::ChineseSimplified);
    }

    /**
     * Configure generator to use Traditional Chinese wordlist.
     *
     * @return self Immutable clone with Traditional Chinese language configured
     */
    public function chineseTraditional(): self
    {
        return $this->useLanguage(BIP39Language::ChineseTraditional);
    }

    /**
     * Configure generator with a specific BIP39 language.
     *
     * Creates an immutable clone with the new language and clears cached wordlist
     * to force reload of the language-specific words.
     *
     * @param  BIP39Language $language The BIP39 language to use
     * @return self          Immutable clone with language configured
     */
    public function useLanguage(BIP39Language $language): self
    {
        $clone = clone $this;
        $clone->language = $language;
        $clone->cachedWords = null;

        return $clone;
    }

    /**
     * Configure the number of words in the generated mnemonic.
     *
     * BIP39 specification requires specific word counts that correspond to
     * entropy and checksum bit lengths: 12, 15, 18, 21, or 24 words.
     *
     * @param int $count Number of words (must be 12, 15, 18, 21, or 24)
     *
     * @throws WordCountNotInAllowedSetException If count is not in the allowed set
     *
     * @return self Immutable clone with word count configured
     */
    public function words(int $count): self
    {
        if (!in_array($count, self::ALLOWED_WORD_COUNTS, true)) {
            throw WordCountNotInAllowedSetException::forCount($count, self::ALLOWED_WORD_COUNTS);
        }

        $clone = clone $this;
        $clone->wordCount = $count;

        return $clone;
    }

    /**
     * Configure a custom word separator overriding the language default.
     *
     * By default, separators are language-specific (ideographic space for Japanese,
     * ASCII space for others). Use this to force a different separator.
     *
     * @param  string $separator Character(s) to place between mnemonic words
     * @return self   Immutable clone with custom separator configured
     */
    public function separator(string $separator): self
    {
        $clone = clone $this;
        $clone->separator = $separator;

        return $clone;
    }

    /**
     * Generate a BIP39 compliant mnemonic phrase.
     *
     * Creates cryptographically secure entropy, calculates SHA256 checksum,
     * splits into 11-bit chunks, and maps to words from the configured language
     * wordlist following BIP39 specification.
     *
     * @return string Space-separated mnemonic phrase (or custom separator if configured)
     */
    public function generate(): string
    {
        $words = $this->getWordList();
        $entropyChunks = $this->generateEntropy();

        $selectedWords = array_map(
            static fn (string $chunk): string => $words[(int) bindec($chunk)],
            $entropyChunks,
        );

        $separator = $this->separator ?? $this->language->getDefaultSeparator();

        return implode($separator, $selectedWords);
    }

    /**
     * Generate multiple independent mnemonic phrases.
     *
     * Each phrase uses fresh cryptographic randomness and is completely
     * independent from the others.
     *
     * @param  int                $count Number of mnemonic phrases to generate
     * @return array<int, string> Indexed array of mnemonic phrases
     */
    public function generateMany(int $count): array
    {
        $mnemonics = [];

        for ($i = 0; $i < $count; ++$i) {
            $mnemonics[] = $this->generate();
        }

        return $mnemonics;
    }

    /**
     * Calculate the total entropy including checksum bits.
     *
     * BIP39 uses 11 bits per word (log2(2048)). This returns the total
     * bits including the checksum portion.
     *
     * @return float Total entropy in bits (word count × 11)
     */
    public function entropy(): float
    {
        // BIP39 uses 11 bits per word
        return (float) ($this->wordCount * 11);
    }

    /**
     * Calculate the raw entropy bits excluding the checksum.
     *
     * Returns the actual random entropy without the checksum bits added
     * by BIP39. For example, 12 words = 132 total bits - 4 checksum bits
     * = 128 bits of raw entropy.
     *
     * @return int Raw entropy bits used for random generation
     */
    public function entropyBits(): int
    {
        $overallBits = $this->wordCount * 11;
        $checksumBits = $this->checksumBits();

        return $overallBits - $checksumBits;
    }

    /**
     * Get the currently configured language.
     *
     * @return BIP39Language The active language for wordlist selection
     */
    public function getLanguage(): BIP39Language
    {
        return $this->language;
    }

    /**
     * Get the currently configured word count.
     *
     * @return int Number of words that will be generated (12, 15, 18, 21, or 24)
     */
    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    /**
     * Calculate the number of checksum bits for the current word count.
     *
     * BIP39 uses a checksum formula: CS = ENT / 32 where ENT is the entropy
     * in bits. This simplifies to: (wordCount - 12) / 3 + 4.
     *
     * @return int Checksum bits (4 for 12 words, 5 for 15 words, etc.)
     */
    private function checksumBits(): int
    {
        // Formula: (wordCount - 12) / 3 + 4
        return (int) ((($this->wordCount - 12) / 3) + 4);
    }

    /**
     * Generate cryptographic entropy and append BIP39 checksum.
     *
     * Creates random bytes using PHP's cryptographically secure random_bytes(),
     * calculates SHA256 checksum according to BIP39, and splits the combined
     * entropy + checksum into 11-bit chunks for word mapping.
     *
     * @return array<int, string> Array of 11-bit binary strings for word selection
     */
    private function generateEntropy(): array
    {
        $entropyBits = $this->entropyBits();
        $checksumBits = $this->checksumBits();

        // Generate random entropy
        /** @var int<1, max> $entropyBytes */
        $entropyBytes = (int) ($entropyBits / 8);
        $entropy = bin2hex(random_bytes($entropyBytes));

        // Calculate checksum
        $checksum = $this->calculateChecksum($entropy, $checksumBits);

        // Convert to binary and split into 11-bit chunks
        $binaryString = $this->hexToBits($entropy).$checksum;

        return mb_str_split($binaryString, 11);
    }

    /**
     * Convert hexadecimal string to binary bit string.
     *
     * Transforms each hex character into its 4-bit binary representation
     * with proper zero-padding for BIP39 entropy processing.
     *
     * @param  string $hex Hexadecimal string to convert
     * @return string Binary string representation (e.g., "0110101001...")
     */
    private function hexToBits(string $hex): string
    {
        $bits = '';
        $length = mb_strlen($hex);

        for ($i = 0; $i < $length; ++$i) {
            $bits .= mb_str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        return $bits;
    }

    /**
     * Calculate BIP39 checksum bits from entropy.
     *
     * Takes the first N bits of the SHA256 hash of the entropy bytes,
     * where N is determined by the entropy length (CS = ENT / 32).
     * This checksum helps detect mnemonic phrase transcription errors.
     *
     * @param  string $entropy Hexadecimal entropy string
     * @param  int    $bits    Number of checksum bits to extract
     * @return string Binary checksum string (e.g., "1010" for 4 bits)
     */
    private function calculateChecksum(string $entropy, int $bits): string
    {
        $hexBin = hex2bin($entropy);
        assert($hexBin !== false);
        $checksumChar = ord(hash('sha256', $hexBin, true)[0]);
        $checksum = '';

        for ($i = 0; $i < $bits; ++$i) {
            $checksum .= ($checksumChar >> (7 - $i)) & 1;
        }

        return $checksum;
    }

    /**
     * Load and cache the wordlist for the configured language.
     *
     * Wordlists are loaded once per language and cached to avoid repeated
     * file I/O operations when generating multiple mnemonics.
     *
     * @return array<int, string> Array of 2048 words indexed 0-2047
     */
    private function getWordList(): array
    {
        if ($this->cachedWords === null) {
            $this->cachedWords = WordListLoader::load($this->language->getFilePath());
        }

        return $this->cachedWords;
    }
}
