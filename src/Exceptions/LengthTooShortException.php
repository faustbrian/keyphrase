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
 * Exception thrown when password or passphrase length is below the minimum.
 *
 * Used to reject length values that are too short to provide adequate
 * cryptographic security.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class LengthTooShortException extends InvalidLengthException
{
    /**
     * Create exception for length values below the required minimum.
     *
     * @param  int  $provided The length value that was provided
     * @param  int  $minimum  The minimum acceptable length value
     * @return self Exception instance with descriptive error message
     */
    public static function forLength(int $provided, int $minimum): self
    {
        return new self(sprintf(
            'Length must be at least %d, got %d.',
            $minimum,
            $provided,
        ));
    }
}
