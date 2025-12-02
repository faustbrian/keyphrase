<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Exceptions;

use function sprintf;

/**
 * Exception thrown when word count is below the required minimum.
 *
 * Used to reject word counts that are too few to provide adequate security.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class WordCountTooFewException extends InvalidWordCountException
{
    /**
     * Create exception for word counts below the required minimum.
     *
     * @param  int  $provided The word count that was provided
     * @param  int  $minimum  The minimum acceptable word count
     * @return self Exception instance with descriptive error message
     */
    public static function forCount(int $provided, int $minimum): self
    {
        return new self(sprintf(
            'Word count must be at least %d, got %d.',
            $minimum,
            $provided,
        ));
    }
}
