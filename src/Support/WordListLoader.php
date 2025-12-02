<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase\Support;

use Cline\Keyphrase\Exceptions\WordListNotFoundException;

use function array_filter;
use function array_key_exists;
use function array_values;
use function explode;
use function file_get_contents;
use function is_readable;
use function realpath;

/**
 * Utility for loading wordlist files with caching.
 *
 * Loads wordlist files from disk and caches them in memory to avoid repeated
 * file I/O operations. Wordlists are expected to be newline-separated text files.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class WordListLoader
{
    /**
     * Cached wordlists keyed by file path.
     *
     * Stores loaded wordlists to prevent repeated disk reads during the
     * same request or long-running process.
     *
     * @var array<string, array<int, string>>
     */
    private static array $cache = [];

    /**
     * Load a wordlist from a file.
     *
     * Reads a newline-separated wordlist file, removes empty lines, and caches
     * the result. Subsequent calls with the same path return the cached version.
     *
     * @param string $path File path to the wordlist
     *
     * @throws WordListNotFoundException When file does not exist or is not readable
     *
     * @return array<int, string> Array of words with sequential integer keys
     */
    public static function load(string $path): array
    {
        if (array_key_exists($path, self::$cache)) {
            return self::$cache[$path];
        }

        $realPath = realpath($path);

        if ($realPath === false || !is_readable($realPath)) {
            throw WordListNotFoundException::forPath($path);
        }

        /** @var string $contents */
        $contents = file_get_contents($realPath);

        $words = array_filter(
            explode("\n", $contents),
            static fn (string $word): bool => $word !== '',
        );

        self::$cache[$path] = array_values($words);

        return self::$cache[$path];
    }

    /**
     * Clear the wordlist cache.
     *
     * Removes all cached wordlists from memory. Useful for testing or
     * when memory usage is a concern in long-running processes.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Preload wordlists into cache.
     *
     * Loads multiple wordlist files into memory in advance, reducing latency
     * for first-time generation requests. Useful for warming up caches at
     * application startup.
     *
     * @param array<string> $paths Array of file paths to wordlists
     */
    public static function preload(array $paths): void
    {
        foreach ($paths as $path) {
            self::load($path);
        }
    }
}
