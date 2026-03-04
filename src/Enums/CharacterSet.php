<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Enums;

use function count_chars;
use function log;
use function mb_strlen;

/**
 * Character sets for password generation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum CharacterSet: string
{
    case Lowercase = 'abcdefghijklmnopqrstuvwxyz';

    case Uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    case Digits = '0123456789';

    case Symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    case AmbiguousSafe = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';

    case Hex = '0123456789abcdef';

    case Alphanumeric = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Merge multiple character sets into a single deduplicated character pool.
     *
     * Combines the characters from multiple sets and removes duplicates to
     * create a unified character pool for password generation. Uses count_chars()
     * for efficient deduplication.
     *
     * @param  array<CharacterSet> $sets Character sets to merge together
     * @return string              Combined string containing all unique characters from the sets
     */
    public static function combine(array $sets): string
    {
        $combined = '';

        foreach ($sets as $set) {
            $combined .= $set->value;
        }

        return count_chars($combined, 3);
    }

    /**
     * Count the total number of unique characters in the character set.
     *
     * @return int The character pool size, used for entropy calculations
     */
    public function length(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * Calculate the entropy bits contributed by each character selection.
     *
     * Entropy per character is calculated as log2(pool size). For example,
     * a 62-character alphanumeric set provides approximately 5.95 bits of
     * entropy per character.
     *
     * @return float Entropy measured in bits for a single character selection
     */
    public function entropyPerCharacter(): float
    {
        return log($this->length(), 2);
    }
}
