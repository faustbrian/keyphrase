<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Contracts;

/**
 * Defines the contract for wordlist providers used in passphrase and mnemonic generation.
 *
 * Wordlist providers supply collections of words for generating human-readable
 * passphrases and mnemonics. They calculate entropy based on wordlist size to
 * measure the cryptographic strength of word selections.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface WordListProvider
{
    /**
     * Retrieve the complete wordlist as an indexed array.
     *
     * @return array<int, string> All words in the wordlist indexed sequentially
     */
    public function getWords(): array;

    /**
     * Select a cryptographically random word from the wordlist.
     *
     * Uses secure random selection to ensure unpredictability for
     * cryptographic applications.
     *
     * @return string A randomly selected word from the wordlist
     */
    public function getRandomWord(): string;

    /**
     * Get the total number of unique words available in the wordlist.
     *
     * @return int The wordlist size, used for entropy calculations
     */
    public function count(): int;

    /**
     * Calculate the entropy bits contributed by selecting one word.
     *
     * Entropy per word is calculated as log2(wordlist size). For example,
     * a 2048-word list provides 11 bits of entropy per word selection.
     *
     * @return float Entropy measured in bits for a single word selection
     */
    public function entropyPerWord(): float;
}
