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
 * Base exception for all passphrase or mnemonic word count validation errors.
 *
 * Abstract base for catching any word count-related validation errors.
 * Use concrete exception classes for specific error conditions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class InvalidWordCountException extends InvalidArgumentException implements KeyphraseException
{
    // Abstract base - no factory methods
}
