<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Generators\MnemonicGenerator;
use Cline\Keyphrase\Generators\PassphraseGenerator;
use Cline\Keyphrase\Generators\PasswordGenerator;
use Cline\Keyphrase\KeyphraseManager;

describe('KeyphraseManager', function (): void {
    describe('factory methods', function (): void {
        it('creates password generator', function (): void {
            $manager = new KeyphraseManager();

            expect($manager->password())->toBeInstanceOf(PasswordGenerator::class);
        });

        it('creates passphrase generator', function (): void {
            $manager = new KeyphraseManager();

            expect($manager->passphrase())->toBeInstanceOf(PassphraseGenerator::class);
        });

        it('creates mnemonic generator', function (): void {
            $manager = new KeyphraseManager();

            expect($manager->mnemonic())->toBeInstanceOf(MnemonicGenerator::class);
        });
    });

    describe('quick generation', function (): void {
        it('generates quick password', function (): void {
            $manager = new KeyphraseManager();
            $password = $manager->quickPassword(20);

            expect($password)->toHaveLength(20);
        });

        it('generates quick secure password', function (): void {
            $manager = new KeyphraseManager();
            $password = $manager->quickSecurePassword(20);

            expect($password)->toHaveLength(20);
        });

        it('generates quick passphrase', function (): void {
            $manager = new KeyphraseManager();
            $passphrase = $manager->quickPassphrase(5);
            $words = explode('-', $passphrase);

            expect($words)->toHaveCount(5);
        });

        it('generates quick mnemonic', function (): void {
            $manager = new KeyphraseManager();
            $mnemonic = $manager->quickMnemonic(12);
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(12);
        });

        it('generates PIN', function (): void {
            $manager = new KeyphraseManager();
            $pin = $manager->pin(6);

            expect($pin)->toMatch('/^\d{6}$/');
        });
    });

    describe('entropy utilities', function (): void {
        it('calculates time to crack', function (): void {
            $manager = new KeyphraseManager();
            $result = $manager->timeToCrack(128);

            expect($result)->toHaveKeys(['seconds', 'human']);
        });

        it('returns strength label', function (): void {
            $manager = new KeyphraseManager();

            expect($manager->strengthLabel(20))->toBe('very weak');
            expect($manager->strengthLabel(128))->toBe('very strong');
        });
    });
});
