<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Support;

use function log;
use function sprintf;

/**
 * Entropy calculation utilities.
 *
 * Provides methods for calculating password entropy, estimating brute-force
 * attack resistance, and classifying password strength. All calculations use
 * information-theoretic entropy measured in bits.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Entropy
{
    /**
     * Calculate entropy in bits for a password of given length and character set size.
     *
     * Uses Shannon entropy formula: entropy = length * log2(characterSetSize).
     * For example, a 16-character password from 62 characters has ~95 bits of entropy.
     *
     * @param  int   $length           Password length
     * @param  int   $characterSetSize Number of unique characters in the character set
     * @return float Entropy in bits
     */
    public static function forPassword(int $length, int $characterSetSize): float
    {
        return $length * log($characterSetSize, 2);
    }

    /**
     * Calculate entropy in bits for a passphrase of given word count and wordlist size.
     *
     * Uses Shannon entropy formula: entropy = wordCount * log2(wordListSize).
     * For example, a 6-word passphrase from EFF's 7,776-word list has ~77 bits of entropy.
     *
     * @param  int   $wordCount    Number of words in the passphrase
     * @param  int   $wordListSize Number of words in the wordlist
     * @return float Entropy in bits
     */
    public static function forPassphrase(int $wordCount, int $wordListSize): float
    {
        return $wordCount * log($wordListSize, 2);
    }

    /**
     * Calculate entropy for a BIP39 mnemonic of given word count.
     *
     * BIP39 mnemonics use a 2048-word list (2^11), providing exactly 11 bits
     * per word. A 12-word mnemonic has 128 bits, 24-word has 256 bits.
     *
     * @param  int   $wordCount Number of words in the mnemonic (12, 15, 18, 21, or 24)
     * @return float Entropy in bits (wordCount * 11)
     */
    public static function forMnemonic(int $wordCount): float
    {
        // BIP39 uses 11 bits per word (2048 words in wordlist = 2^11)
        return $wordCount * 11.0;
    }

    /**
     * Estimate time to crack at given attempts per second.
     *
     * Calculates average brute-force crack time assuming uniform random search
     * (requires searching half the keyspace on average). Default assumes 1 billion
     * attempts per second, representing a powerful offline attack.
     *
     * @param  float                                $entropyBits       Entropy in bits
     * @param  int                                  $attemptsPerSecond Attacker's guessing rate (default: 1 billion/sec)
     * @return array{seconds: float, human: string} Crack time in seconds and human-readable format
     */
    public static function timeToCrack(float $entropyBits, int $attemptsPerSecond = 1_000_000_000): array
    {
        $combinations = 2 ** $entropyBits;
        $averageAttempts = $combinations / 2;
        $seconds = $averageAttempts / $attemptsPerSecond;

        return [
            'seconds' => $seconds,
            'human' => self::humanReadableTime($seconds),
        ];
    }

    /**
     * Classify password strength based on entropy.
     *
     * Uses industry-standard thresholds: very weak (< 28 bits), weak (28-35 bits),
     * reasonable (36-59 bits), strong (60-127 bits), very strong (>= 128 bits).
     *
     * @param  float  $entropyBits Entropy in bits
     * @return string Strength label (very weak, weak, reasonable, strong, very strong)
     */
    public static function strengthLabel(float $entropyBits): string
    {
        return match (true) {
            $entropyBits < 28 => 'very weak',
            $entropyBits < 36 => 'weak',
            $entropyBits < 60 => 'reasonable',
            $entropyBits < 128 => 'strong',
            default => 'very strong',
        };
    }

    /**
     * Convert seconds to human-readable time string.
     *
     * Formats time duration using the largest appropriate unit (seconds, minutes,
     * hours, days, weeks, months, years) with magnitude prefixes (thousand, million,
     * billion, trillion) when necessary.
     *
     * @param  float  $seconds Duration in seconds
     * @return string Human-readable time string (e.g., "2.5 million years", "centuries")
     */
    private static function humanReadableTime(float $seconds): string
    {
        if ($seconds < 1) {
            return 'instant';
        }

        $units = [
            'year' => 31_536_000,
            'month' => 2_592_000,
            'week' => 604_800,
            'day' => 86_400,
            'hour' => 3_600,
            'minute' => 60,
            'second' => 1,
        ];

        foreach ($units as $name => $divisor) {
            if ($seconds >= $divisor) {
                $value = $seconds / $divisor;

                if ($value >= 1e15) {
                    return 'centuries';
                }

                if ($value >= 1e12) {
                    return sprintf('%.1f trillion %ss', $value / 1e12, $name);
                }

                if ($value >= 1e9) {
                    return sprintf('%.1f billion %ss', $value / 1e9, $name);
                }

                if ($value >= 1e6) {
                    return sprintf('%.1f million %ss', $value / 1e6, $name);
                }

                if ($value >= 1e3) {
                    return sprintf('%.1f thousand %ss', $value / 1e3, $name);
                }

                return sprintf('%.1f %ss', $value, $name);
            }
        }

        // @codeCoverageIgnoreStart
        return 'instant';
        // @codeCoverageIgnoreEnd
    }
}
