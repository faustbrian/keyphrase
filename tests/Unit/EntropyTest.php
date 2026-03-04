<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Support\Entropy;

describe('Entropy', function (): void {
    describe('forPassword', function (): void {
        it('calculates entropy for password', function (): void {
            // 8 character password with 26 lowercase letters
            $entropy = Entropy::forPassword(8, 26);

            expect($entropy)->toBeGreaterThan(37)
                ->toBeLessThan(38);
        });

        it('increases with length', function (): void {
            $entropy8 = Entropy::forPassword(8, 62);
            $entropy16 = Entropy::forPassword(16, 62);

            expect($entropy16)->toBe($entropy8 * 2);
        });

        it('increases with character set size', function (): void {
            $entropySmall = Entropy::forPassword(10, 26);
            $entropyLarge = Entropy::forPassword(10, 62);

            expect($entropyLarge)->toBeGreaterThan($entropySmall);
        });
    });

    describe('forPassphrase', function (): void {
        it('calculates entropy for passphrase', function (): void {
            // 6 words from 7776 word list
            $entropy = Entropy::forPassphrase(6, 7_776);

            expect($entropy)->toBeGreaterThan(77)
                ->toBeLessThan(78);
        });
    });

    describe('forMnemonic', function (): void {
        it('calculates 132 bits for 12 words', function (): void {
            expect(Entropy::forMnemonic(12))->toBe(132.0);
        });

        it('calculates 264 bits for 24 words', function (): void {
            expect(Entropy::forMnemonic(24))->toBe(264.0);
        });
    });

    describe('timeToCrack', function (): void {
        it('returns instant for low entropy', function (): void {
            $result = Entropy::timeToCrack(10);

            expect($result['human'])->toBe('instant');
        });

        it('returns seconds for medium entropy', function (): void {
            $result = Entropy::timeToCrack(30);

            expect($result['seconds'])->toBeGreaterThan(0);
        });

        it('returns centuries for high entropy', function (): void {
            $result = Entropy::timeToCrack(256);

            expect($result['human'])->toBe('centuries');
        });

        it('returns trillion format for very high entropy', function (): void {
            // Need ~96 bits for trillion years range
            $result = Entropy::timeToCrack(100);

            expect($result['human'])->toContain('trillion');
        });

        it('returns billion format for high entropy', function (): void {
            // Need ~86 bits for billion years range
            $result = Entropy::timeToCrack(87);

            expect($result['human'])->toContain('billion');
        });

        it('returns million format for moderate entropy', function (): void {
            // Need ~76 bits for million years range
            $result = Entropy::timeToCrack(77);

            expect($result['human'])->toContain('million');
        });

        it('returns thousand format for lower entropy', function (): void {
            // Need ~66 bits for thousand years range
            $result = Entropy::timeToCrack(67);

            expect($result['human'])->toContain('thousand');
        });

        it('returns plain number format for small values', function (): void {
            // Around 60 bits gives regular years
            $result = Entropy::timeToCrack(60);

            // Should be something like "X.X years"
            expect($result['human'])->toMatch('/^\d+\.\d+ \w+s$/');
        });
    });

    describe('strengthLabel', function (): void {
        it('returns very weak for entropy < 28', function (): void {
            expect(Entropy::strengthLabel(20))->toBe('very weak');
        });

        it('returns weak for entropy 28-35', function (): void {
            expect(Entropy::strengthLabel(30))->toBe('weak');
        });

        it('returns reasonable for entropy 36-59', function (): void {
            expect(Entropy::strengthLabel(50))->toBe('reasonable');
        });

        it('returns strong for entropy 60-127', function (): void {
            expect(Entropy::strengthLabel(80))->toBe('strong');
        });

        it('returns very strong for entropy >= 128', function (): void {
            expect(Entropy::strengthLabel(128))->toBe('very strong');
            expect(Entropy::strengthLabel(256))->toBe('very strong');
        });
    });
});
