<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Enums\EFFWordList;

describe('EFFWordList', function (): void {
    describe('getFilePath', function (): void {
        it('returns correct path for large wordlist', function (): void {
            $path = EFFWordList::Large->getFilePath();

            expect($path)->toContain('WordLists/EFF/eff_large_wordlist.txt');
        });

        it('returns correct path for short wordlist', function (): void {
            $path = EFFWordList::Short->getFilePath();

            expect($path)->toContain('WordLists/EFF/eff_short_wordlist_1.txt');
        });

        it('returns correct path for unique prefix wordlist', function (): void {
            $path = EFFWordList::UniquePrefix->getFilePath();

            expect($path)->toContain('WordLists/EFF/eff_short_wordlist_2_0.txt');
        });
    });

    describe('getWordCount', function (): void {
        it('returns 7776 for large wordlist', function (): void {
            expect(EFFWordList::Large->getWordCount())->toBe(7_776);
        });

        it('returns 1296 for short wordlist', function (): void {
            expect(EFFWordList::Short->getWordCount())->toBe(1_296);
        });

        it('returns 1296 for unique prefix wordlist', function (): void {
            expect(EFFWordList::UniquePrefix->getWordCount())->toBe(1_296);
        });
    });

    describe('getEntropyPerWord', function (): void {
        it('returns ~12.9 for large wordlist', function (): void {
            $entropy = EFFWordList::Large->getEntropyPerWord();

            expect($entropy)->toBe(12.925);
        });

        it('returns ~10.3 for short wordlist', function (): void {
            $entropy = EFFWordList::Short->getEntropyPerWord();

            expect($entropy)->toBe(10.34);
        });

        it('returns ~10.3 for unique prefix wordlist', function (): void {
            $entropy = EFFWordList::UniquePrefix->getEntropyPerWord();

            expect($entropy)->toBe(10.34);
        });
    });
});
