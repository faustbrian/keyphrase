<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase;

use Cline\Keyphrase\Generators\MnemonicGenerator;
use Cline\Keyphrase\Generators\PassphraseGenerator;
use Cline\Keyphrase\Generators\PasswordGenerator;
use Cline\Keyphrase\Support\Entropy;

/**
 * Main manager for all phrase generation.
 *
 * Provides factory methods for creating fluent generators for passwords,
 * passphrases, and BIP39 mnemonics, along with convenience methods for
 * quick generation and entropy analysis.
 *
 * ```php
 * // Generate a password
 * $password = $manager->password()->length(16)->withSymbols()->generate();
 *
 * // Generate a passphrase
 * $passphrase = $manager->passphrase()->words(6)->titleCase()->generate();
 *
 * // Generate a mnemonic
 * $mnemonic = $manager->mnemonic()->words(24)->english()->generate();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class KeyphraseManager
{
    /**
     * Create a new password generator.
     *
     * Returns a fluent generator for configuring and generating passwords
     * with customizable character sets, length, and constraints.
     */
    public function password(): PasswordGenerator
    {
        return new PasswordGenerator();
    }

    /**
     * Create a new passphrase generator.
     *
     * Returns a fluent generator for configuring and generating passphrases
     * from EFF diceware wordlists with customizable word count, separators,
     * and case transformations.
     */
    public function passphrase(): PassphraseGenerator
    {
        return new PassphraseGenerator();
    }

    /**
     * Create a new BIP39 mnemonic generator.
     *
     * Returns a fluent generator for creating BIP39-compliant mnemonics
     * for cryptocurrency wallets and key derivation.
     */
    public function mnemonic(): MnemonicGenerator
    {
        return new MnemonicGenerator();
    }

    /**
     * Generate a quick alphanumeric password.
     *
     * Convenience method for generating a password with uppercase letters,
     * lowercase letters, and digits, but no special symbols.
     *
     * @param int $length Password length (default: 16)
     */
    public function quickPassword(int $length = 16): string
    {
        return $this->password()->length($length)->alphanumeric()->generate();
    }

    /**
     * Generate a quick secure password with symbols.
     *
     * Convenience method for generating a high-security password with
     * all character types including special symbols.
     *
     * @param int $length Password length (default: 20)
     */
    public function quickSecurePassword(int $length = 20): string
    {
        return $this->password()
            ->length($length)
            ->withLowercase()
            ->withUppercase()
            ->withDigits()
            ->withSymbols()
            ->generate();
    }

    /**
     * Generate a quick passphrase.
     *
     * Convenience method for generating a passphrase with default settings
     * using the large EFF wordlist.
     *
     * @param int $words Number of words (default: 6)
     */
    public function quickPassphrase(int $words = 6): string
    {
        return $this->passphrase()->words($words)->generate();
    }

    /**
     * Generate a quick mnemonic.
     *
     * Convenience method for generating a BIP39 mnemonic with default
     * English wordlist.
     *
     * @param int $words Number of words (default: 12, must be 12, 15, 18, 21, or 24)
     */
    public function quickMnemonic(int $words = 12): string
    {
        return $this->mnemonic()->words($words)->generate();
    }

    /**
     * Generate a PIN code.
     *
     * Convenience method for generating a numeric PIN using only digits.
     *
     * @param int $length PIN length (default: 6)
     */
    public function pin(int $length = 6): string
    {
        return $this->password()->length($length)->pin()->generate();
    }

    /**
     * Calculate time to crack a password at given attempts/second.
     *
     * Estimates the average time required to crack a password through brute force
     * based on its entropy and attacker's computational capacity.
     *
     * @param  float                                $entropyBits       Entropy in bits (from generator->entropy())
     * @param  int                                  $attemptsPerSecond Attacker's guessing rate (default: 1 billion/sec)
     * @return array{seconds: float, human: string} Crack time in seconds and human-readable format
     */
    public function timeToCrack(float $entropyBits, int $attemptsPerSecond = 1_000_000_000): array
    {
        return Entropy::timeToCrack($entropyBits, $attemptsPerSecond);
    }

    /**
     * Get a human-readable strength label for given entropy.
     *
     * Classifies password strength into categories: very weak (< 28 bits),
     * weak (28-36 bits), reasonable (36-60 bits), strong (60-128 bits),
     * or very strong (>= 128 bits).
     *
     * @param  float  $entropyBits Entropy in bits (from generator->entropy())
     * @return string Strength label (very weak, weak, reasonable, strong, very strong)
     */
    public function strengthLabel(float $entropyBits): string
    {
        return Entropy::strengthLabel($entropyBits);
    }
}
