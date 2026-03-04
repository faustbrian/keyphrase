<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Support;

use function count;
use function mb_strlen;
use function mb_substr;
use function random_bytes;
use function random_int;

/**
 * Cryptographically secure random number generation utilities.
 *
 * Provides wrapper methods around PHP's CSPRNG functions (random_int, random_bytes)
 * for generating cryptographically secure random values suitable for password
 * generation and security-sensitive applications.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SecureRandom
{
    /**
     * Generate a cryptographically secure random integer.
     *
     * Uses PHP's random_int() which relies on the system's CSPRNG
     * (e.g., /dev/urandom on Unix, CryptGenRandom on Windows).
     *
     * @param  int $min Minimum value (inclusive)
     * @param  int $max Maximum value (inclusive)
     * @return int Random integer in range [min, max]
     */
    public static function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * Generate cryptographically secure random bytes.
     *
     * Uses PHP's random_bytes() which relies on the system's CSPRNG.
     * The returned bytes are suitable for cryptographic key generation.
     *
     * @param  int<1, max> $length Number of bytes to generate
     * @return string      Binary string of random bytes
     */
    public static function bytes(int $length): string
    {
        return random_bytes($length);
    }

    /**
     * Select a random element from an array.
     *
     * Uses cryptographically secure random selection, suitable for selecting
     * words from wordlists in security-sensitive applications.
     *
     * @template T
     *
     * @param  array<int, T> $array Array to select from
     * @return T             Randomly selected element
     */
    public static function element(array $array): mixed
    {
        return $array[self::int(0, count($array) - 1)];
    }

    /**
     * Select multiple random elements from an array.
     *
     * Selects with replacement, meaning the same element can be selected multiple
     * times. Each selection is independent and cryptographically secure.
     *
     * @template T
     *
     * @param  array<int, T> $array Array to select from
     * @param  int           $count Number of elements to select
     * @return array<int, T> Array of randomly selected elements
     */
    public static function elements(array $array, int $count): array
    {
        $selected = [];
        $max = count($array) - 1;

        for ($i = 0; $i < $count; ++$i) {
            $selected[] = $array[self::int(0, $max)];
        }

        return $selected;
    }

    /**
     * Generate a random string from a character set.
     *
     * Selects characters independently and uniformly from the provided character set
     * using cryptographically secure random number generation. Supports multibyte
     * characters.
     *
     * @param  string $characters Character set to select from
     * @param  int    $length     Length of the string to generate
     * @return string Randomly generated string
     */
    public static function string(string $characters, int $length): string
    {
        $result = '';
        $max = mb_strlen($characters) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $result .= mb_substr($characters, self::int(0, $max), 1);
        }

        return $result;
    }
}
