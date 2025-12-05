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
 * Exception thrown when password or passphrase length is not positive.
 *
 * Used to reject length values that are zero or negative.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class LengthMustBePositiveException extends InvalidLengthException
{
    /**
     * Create exception for non-positive length values.
     *
     * @param  int  $provided The invalid length value (zero or negative)
     * @return self Exception instance with descriptive error message
     */
    public static function forLength(int $provided): self
    {
        return new self(sprintf(
            'Length must be a positive integer, got %d.',
            $provided,
        ));
    }
}
