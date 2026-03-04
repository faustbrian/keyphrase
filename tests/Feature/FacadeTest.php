<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Facades\Keyphrase;
use Cline\Keyphrase\Generators\MnemonicGenerator;
use Cline\Keyphrase\Generators\PassphraseGenerator;
use Cline\Keyphrase\Generators\PasswordGenerator;

describe('Keyphrase Facade', function (): void {
    describe('generator access', function (): void {
        it('provides password generator via facade', function (): void {
            expect(Keyphrase::password())->toBeInstanceOf(PasswordGenerator::class);
        });

        it('provides passphrase generator via facade', function (): void {
            expect(Keyphrase::passphrase())->toBeInstanceOf(PassphraseGenerator::class);
        });

        it('provides mnemonic generator via facade', function (): void {
            expect(Keyphrase::mnemonic())->toBeInstanceOf(MnemonicGenerator::class);
        });
    });

    describe('quick generation via facade', function (): void {
        it('generates quick password', function (): void {
            $password = Keyphrase::quickPassword(16);

            expect($password)->toHaveLength(16);
        });

        it('generates quick secure password', function (): void {
            $password = Keyphrase::quickSecurePassword(20);

            expect($password)->toHaveLength(20);
        });

        it('generates quick passphrase', function (): void {
            $passphrase = Keyphrase::quickPassphrase(4);
            $words = explode('-', $passphrase);

            expect($words)->toHaveCount(4);
        });

        it('generates quick mnemonic', function (): void {
            $mnemonic = Keyphrase::quickMnemonic(12);
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(12);
        });

        it('generates PIN', function (): void {
            $pin = Keyphrase::pin(4);

            expect($pin)->toMatch('/^\d{4}$/');
        });
    });

    describe('fluent usage via facade', function (): void {
        it('chains password generator methods', function (): void {
            $password = Keyphrase::password()
                ->length(20)
                ->withSymbols()
                ->excludeAmbiguous()
                ->generate();

            expect($password)->toHaveLength(20);
        });

        it('chains passphrase generator methods', function (): void {
            $passphrase = Keyphrase::passphrase()
                ->words(5)
                ->separator('_')
                ->titleCase()
                ->generate();

            expect($passphrase)->toContain('_');
        });

        it('chains mnemonic generator methods', function (): void {
            $mnemonic = Keyphrase::mnemonic()
                ->words(24)
                ->english()
                ->generate();

            $words = explode(' ', $mnemonic);
            expect($words)->toHaveCount(24);
        });
    });

    describe('entropy utilities via facade', function (): void {
        it('calculates time to crack', function (): void {
            $result = Keyphrase::timeToCrack(80);

            expect($result)->toHaveKeys(['seconds', 'human']);
        });

        it('returns strength label', function (): void {
            expect(Keyphrase::strengthLabel(80))->toBe('strong');
        });
    });
});
