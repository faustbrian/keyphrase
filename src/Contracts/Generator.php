<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Contracts;

/**
 * Defines the contract for cryptographic phrase generators.
 *
 * Implementations generate various types of secure phrases including passwords,
 * passphrases, and mnemonic phrases. Each generator calculates entropy to measure
 * the cryptographic strength of generated output.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface Generator
{
    /**
     * Generate a single cryptographic phrase using the current configuration.
     *
     * @return string The generated phrase (password, passphrase, or mnemonic)
     */
    public function generate(): string;

    /**
     * Generate multiple cryptographic phrases with identical configuration.
     *
     * Each generated phrase is independent and uses fresh randomness.
     *
     * @param  int                $count Number of phrases to generate (must be positive)
     * @return array<int, string> Indexed array of generated phrases
     */
    public function generateMany(int $count): array;

    /**
     * Calculate the cryptographic strength in bits of entropy.
     *
     * Entropy measures the unpredictability of the generated output based on
     * the current configuration (character sets, word lists, length, etc.).
     * Higher entropy values indicate stronger cryptographic security.
     *
     * @return float Entropy measured in bits
     */
    public function entropy(): float;
}
