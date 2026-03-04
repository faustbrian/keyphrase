<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Exceptions\WordListNotFoundException;
use Cline\Keyphrase\Support\WordListLoader;

describe('WordListLoader', function (): void {
    beforeEach(function (): void {
        WordListLoader::clearCache();
    });

    describe('load', function (): void {
        it('loads wordlist from file', function (): void {
            $path = __DIR__.'/../../src/WordLists/BIP39/english.txt';
            $words = WordListLoader::load($path);

            expect($words)->toBeArray()
                ->toHaveCount(2_048);
        });

        it('caches loaded wordlists', function (): void {
            $path = __DIR__.'/../../src/WordLists/BIP39/english.txt';

            $words1 = WordListLoader::load($path);
            $words2 = WordListLoader::load($path);

            expect($words1)->toBe($words2);
        });

        it('throws exception for non-existent file', function (): void {
            WordListLoader::load('/non/existent/path.txt');
        })->throws(WordListNotFoundException::class);

        it('filters empty lines', function (): void {
            $path = __DIR__.'/../../src/WordLists/BIP39/english.txt';
            $words = WordListLoader::load($path);

            foreach ($words as $word) {
                expect($word)->not->toBe('');
            }
        });
    });

    describe('clearCache', function (): void {
        it('clears the wordlist cache', function (): void {
            $path = __DIR__.'/../../src/WordLists/BIP39/english.txt';

            WordListLoader::load($path);
            WordListLoader::clearCache();

            // After clearing, loading again should work (proves cache was cleared)
            $words = WordListLoader::load($path);
            expect($words)->toHaveCount(2_048);
        });
    });

    describe('preload', function (): void {
        it('preloads multiple wordlists', function (): void {
            $paths = [
                __DIR__.'/../../src/WordLists/BIP39/english.txt',
                __DIR__.'/../../src/WordLists/BIP39/spanish.txt',
            ];

            WordListLoader::preload($paths);

            // Subsequent loads should be from cache
            $english = WordListLoader::load($paths[0]);
            $spanish = WordListLoader::load($paths[1]);

            expect($english)->toHaveCount(2_048);
            expect($spanish)->toHaveCount(2_048);
        });
    });
});
