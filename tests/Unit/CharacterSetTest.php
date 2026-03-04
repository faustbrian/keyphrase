<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Enums\CharacterSet;

describe('CharacterSet', function (): void {
    describe('length', function (): void {
        it('returns correct length for lowercase', function (): void {
            expect(CharacterSet::Lowercase->length())->toBe(26);
        });

        it('returns correct length for uppercase', function (): void {
            expect(CharacterSet::Uppercase->length())->toBe(26);
        });

        it('returns correct length for digits', function (): void {
            expect(CharacterSet::Digits->length())->toBe(10);
        });

        it('returns correct length for symbols', function (): void {
            expect(CharacterSet::Symbols->length())->toBe(26);
        });

        it('returns correct length for alphanumeric', function (): void {
            expect(CharacterSet::Alphanumeric->length())->toBe(62);
        });

        it('returns correct length for hex', function (): void {
            expect(CharacterSet::Hex->length())->toBe(16);
        });
    });

    describe('entropyPerCharacter', function (): void {
        it('calculates entropy for lowercase', function (): void {
            // log2(26) ≈ 4.7
            $entropy = CharacterSet::Lowercase->entropyPerCharacter();

            expect($entropy)->toBeGreaterThan(4.7)
                ->toBeLessThan(4.8);
        });

        it('calculates entropy for alphanumeric', function (): void {
            // log2(62) ≈ 5.95
            $entropy = CharacterSet::Alphanumeric->entropyPerCharacter();

            expect($entropy)->toBeGreaterThan(5.9)
                ->toBeLessThan(6.0);
        });

        it('calculates entropy for digits', function (): void {
            // log2(10) ≈ 3.32
            $entropy = CharacterSet::Digits->entropyPerCharacter();

            expect($entropy)->toBeGreaterThan(3.3)
                ->toBeLessThan(3.4);
        });
    });

    describe('combine', function (): void {
        it('combines multiple character sets', function (): void {
            $combined = CharacterSet::combine([
                CharacterSet::Lowercase,
                CharacterSet::Digits,
            ]);

            expect($combined)->toContain('a')
                ->toContain('z')
                ->toContain('0')
                ->toContain('9');
        });

        it('removes duplicate characters when combining', function (): void {
            $combined = CharacterSet::combine([
                CharacterSet::Lowercase,
                CharacterSet::Alphanumeric,
            ]);

            // Should only have unique characters
            expect(mb_strlen($combined))->toBe(62);
        });

        it('combines all character sets', function (): void {
            $combined = CharacterSet::combine([
                CharacterSet::Lowercase,
                CharacterSet::Uppercase,
                CharacterSet::Digits,
                CharacterSet::Symbols,
            ]);

            expect($combined)->toContain('a')
                ->toContain('A')
                ->toContain('0')
                ->toContain('!');
        });
    });
});
