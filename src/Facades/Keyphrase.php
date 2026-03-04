<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Facades;

use Cline\Keyphrase\Generators\MnemonicGenerator;
use Cline\Keyphrase\Generators\PassphraseGenerator;
use Cline\Keyphrase\Generators\PasswordGenerator;
use Cline\Keyphrase\KeyphraseManager;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade for cryptographic phrase generation.
 *
 * Provides static access to password, passphrase, and mnemonic generators
 * with fluent configuration interfaces. Includes convenience methods for
 * quick generation and security analysis utilities.
 *
 * ```php
 * // Generate a password
 * $password = Keyphrase::password()->length(16)->withSymbols()->generate();
 *
 * // Generate a passphrase
 * $passphrase = Keyphrase::passphrase()->words(6)->titleCase()->generate();
 *
 * // Generate a mnemonic
 * $mnemonic = Keyphrase::mnemonic()->words(24)->english()->generate();
 *
 * // Quick generation
 * $pin = Keyphrase::pin(6);
 * $quick = Keyphrase::quickPassword(16);
 * ```
 *
 * @method static MnemonicGenerator                    mnemonic()                                                           Create a new BIP39 mnemonic phrase generator
 * @method static PassphraseGenerator                  passphrase()                                                         Create a new word-based passphrase generator
 * @method static PasswordGenerator                    password()                                                           Create a new character-based password generator
 * @method static string                               pin(int $length = 6)                                                 Generate a numeric PIN code of specified length
 * @method static string                               quickMnemonic(int $words = 12)                                       Generate a 12-word English BIP39 mnemonic phrase
 * @method static string                               quickPassphrase(int $words = 6)                                      Generate a 6-word passphrase using EFF large wordlist
 * @method static string                               quickPassword(int $length = 16)                                      Generate a 16-character alphanumeric password
 * @method static string                               quickSecurePassword(int $length = 20)                                Generate a 20-character password with symbols included
 * @method static string                               strengthLabel(float $entropyBits)                                    Get human-readable strength label for entropy value
 * @method static array{seconds: float, human: string} timeToCrack(float $entropyBits, int $attemptsPerSecond = 1000000000) Calculate estimated time to crack based on entropy
 *
 * @author Brian Faust <brian@cline.sh>
 * @see KeyphraseManager
 */
final class Keyphrase extends Facade
{
    /**
     * Get the service container binding key for the facade.
     *
     * @return string The fully qualified class name of KeyphraseManager
     */
    protected static function getFacadeAccessor(): string
    {
        return KeyphraseManager::class;
    }
}
