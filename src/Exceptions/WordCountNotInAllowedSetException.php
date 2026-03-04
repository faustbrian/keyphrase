<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Exceptions;

use function implode;
use function sprintf;

/**
 * Exception thrown when word count is not in the allowed set of values.
 *
 * Used when specific word counts are required by specification, such as
 * BIP39 requiring exactly 12, 15, 18, 21, or 24 words.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class WordCountNotInAllowedSetException extends InvalidWordCountException
{
    /**
     * Create exception for word counts not in the allowed set of values.
     *
     * @param  int             $provided The word count that was provided
     * @param  array<int, int> $allowed  Valid word count values accepted
     * @return self            Exception instance listing all allowed values
     */
    public static function forCount(int $provided, array $allowed): self
    {
        return new self(sprintf(
            'Word count must be one of [%s], got %d.',
            implode(', ', $allowed),
            $provided,
        ));
    }
}
