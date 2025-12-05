<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Generators;

use Cline\Keyphrase\Contracts\Generator;
use Cline\Keyphrase\Enums\CharacterSet;
use Cline\Keyphrase\Exceptions\LengthMustBePositiveException;
use Cline\Keyphrase\Support\SecureRandom;

use function count_chars;
use function log;
use function mb_str_split;
use function mb_strlen;
use function str_replace;

/**
 * Fluent password generator with configurable character sets and constraints.
 *
 * Generates cryptographically secure passwords with flexible character set configuration,
 * including support for ambiguous character exclusion and custom character sets.
 * Uses immutable fluent interface pattern for configuration.
 *
 * ```php
 * $password = Keyphrase::password()
 *     ->length(16)
 *     ->withLowercase()
 *     ->withUppercase()
 *     ->withDigits()
 *     ->withSymbols()
 *     ->generate();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PasswordGenerator implements Generator
{
    /**
     * Length of the generated password.
     */
    private int $length = 16;

    /**
     * Whether to include lowercase letters (a-z).
     */
    private bool $includeLowercase = true;

    /**
     * Whether to include uppercase letters (A-Z).
     */
    private bool $includeUppercase = true;

    /**
     * Whether to include digits (0-9).
     */
    private bool $includeDigits = true;

    /**
     * Whether to include special symbols.
     */
    private bool $includeSymbols = false;

    /**
     * Whether to exclude visually ambiguous characters.
     */
    private bool $excludeAmbiguous = false;

    /**
     * Additional custom characters to include in the character set.
     */
    private string $customCharacters = '';

    /**
     * Specific characters to exclude from the password.
     */
    private string $excludeCharacters = '';

    /**
     * Create a new password generator instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the password length.
     *
     * Longer passwords provide higher entropy. For a full character set (62 chars),
     * 16 characters provides ~95 bits of entropy, while 20 provides ~119 bits.
     *
     * @param int $length Password length (minimum 1)
     *
     * @throws LengthMustBePositiveException When length is less than 1
     */
    public function length(int $length): self
    {
        if ($length < 1) {
            throw LengthMustBePositiveException::forLength($length);
        }

        $clone = clone $this;
        $clone->length = $length;

        return $clone;
    }

    /**
     * Include lowercase letters (a-z).
     *
     * @param bool $include Whether to include lowercase letters
     */
    public function withLowercase(bool $include = true): self
    {
        $clone = clone $this;
        $clone->includeLowercase = $include;

        return $clone;
    }

    /**
     * Include uppercase letters (A-Z).
     *
     * @param bool $include Whether to include uppercase letters
     */
    public function withUppercase(bool $include = true): self
    {
        $clone = clone $this;
        $clone->includeUppercase = $include;

        return $clone;
    }

    /**
     * Include digits (0-9).
     *
     * @param bool $include Whether to include digits
     */
    public function withDigits(bool $include = true): self
    {
        $clone = clone $this;
        $clone->includeDigits = $include;

        return $clone;
    }

    /**
     * Include special symbols (!@#$%...).
     *
     * @param bool $include Whether to include symbols
     */
    public function withSymbols(bool $include = true): self
    {
        $clone = clone $this;
        $clone->includeSymbols = $include;

        return $clone;
    }

    /**
     * Exclude ambiguous characters (0O, 1lI, etc.).
     *
     * When enabled, removes visually similar characters that can be confused
     * when transcribing passwords manually. Overrides individual character
     * set inclusions when enabled.
     *
     * @param bool $exclude Whether to exclude ambiguous characters
     */
    public function excludeAmbiguous(bool $exclude = true): self
    {
        $clone = clone $this;
        $clone->excludeAmbiguous = $exclude;

        return $clone;
    }

    /**
     * Add custom characters to the character set.
     *
     * @param string $characters Additional characters to include
     */
    public function withCustomCharacters(string $characters): self
    {
        $clone = clone $this;
        $clone->customCharacters = $characters;

        return $clone;
    }

    /**
     * Exclude specific characters from the password.
     *
     * Useful for removing characters that may cause issues in specific contexts,
     * such as shell metacharacters or characters that require escaping.
     *
     * @param string $characters Characters to exclude from the final password
     */
    public function exclude(string $characters): self
    {
        $clone = clone $this;
        $clone->excludeCharacters = $characters;

        return $clone;
    }

    /**
     * Create an alphanumeric password (letters and numbers only).
     */
    public function alphanumeric(): self
    {
        return $this
            ->withLowercase()
            ->withUppercase()
            ->withDigits()
            ->withSymbols(false);
    }

    /**
     * Create a PIN (digits only).
     */
    public function pin(): self
    {
        return $this
            ->withLowercase(false)
            ->withUppercase(false)
            ->withDigits()
            ->withSymbols(false);
    }

    /**
     * Create a hex password.
     *
     * Generates password using hexadecimal characters (0-9, a-f) only.
     */
    public function hex(): self
    {
        return $this
            ->withLowercase(false)
            ->withUppercase(false)
            ->withDigits(false)
            ->withSymbols(false)
            ->withCustomCharacters(CharacterSet::Hex->value);
    }

    /**
     * Generate the password.
     *
     * Creates a password by randomly selecting characters from the configured
     * character set using cryptographically secure random number generation.
     *
     * @return string The generated password
     */
    public function generate(): string
    {
        $characterSet = $this->buildCharacterSet();

        return SecureRandom::string($characterSet, $this->length);
    }

    /**
     * Generate multiple passwords.
     *
     * Each password is independently generated with cryptographically
     * secure randomness.
     *
     * @param  int                $count Number of passwords to generate
     * @return array<int, string>
     */
    public function generateMany(int $count): array
    {
        $passwords = [];

        for ($i = 0; $i < $count; ++$i) {
            $passwords[] = $this->generate();
        }

        return $passwords;
    }

    /**
     * Calculate the entropy in bits.
     *
     * Calculates entropy based on character set size and password length using
     * the formula: entropy = length * log2(characterSetSize).
     *
     * @return float Entropy in bits
     */
    public function entropy(): float
    {
        $characterSet = $this->buildCharacterSet();

        return $this->length * log(mb_strlen($characterSet), 2);
    }

    /**
     * Build the character set based on configuration.
     *
     * Assembles the final character set by combining enabled character types,
     * applying exclusions, and deduplicating characters to ensure each character
     * appears exactly once.
     *
     * @return string Deduplicated character set for password generation
     */
    private function buildCharacterSet(): string
    {
        $characters = '';

        if ($this->excludeAmbiguous) {
            $characters = CharacterSet::AmbiguousSafe->value;
        } else {
            if ($this->includeLowercase) {
                $characters .= CharacterSet::Lowercase->value;
            }

            if ($this->includeUppercase) {
                $characters .= CharacterSet::Uppercase->value;
            }

            if ($this->includeDigits) {
                $characters .= CharacterSet::Digits->value;
            }
        }

        if ($this->includeSymbols) {
            $characters .= CharacterSet::Symbols->value;
        }

        $characters .= $this->customCharacters;

        if ($this->excludeCharacters !== '') {
            $characters = str_replace(
                mb_str_split($this->excludeCharacters),
                '',
                $characters,
            );
        }

        // Deduplicate characters
        return count_chars($characters, 3);
    }
}
