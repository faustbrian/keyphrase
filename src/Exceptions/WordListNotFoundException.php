<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Exceptions;

use RuntimeException;

use function sprintf;

/**
 * Exception thrown when a wordlist file cannot be located or accessed.
 *
 * Indicates that the wordlist file required for passphrase or mnemonic
 * generation is missing from the expected filesystem location or cannot
 * be read due to permission issues.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class WordListNotFoundException extends RuntimeException implements KeyphraseException
{
    /**
     * Create exception for a missing or unreadable wordlist file.
     *
     * @param  string $path The filesystem path where the wordlist was expected
     * @return self   Exception instance with the problematic file path
     */
    public static function forPath(string $path): self
    {
        return new self(sprintf(
            'Wordlist file not found or unreadable: %s',
            $path,
        ));
    }
}
