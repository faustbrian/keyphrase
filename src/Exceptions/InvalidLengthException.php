<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Exceptions;

use InvalidArgumentException;

/**
 * Base exception for all password or passphrase length validation errors.
 *
 * Abstract base for catching any length-related validation errors.
 * Use concrete exception classes for specific error conditions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class InvalidLengthException extends InvalidArgumentException implements KeyphraseException
{
    // Abstract base - no factory methods
}
