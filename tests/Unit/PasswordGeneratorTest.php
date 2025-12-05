<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Exceptions\LengthMustBePositiveException;
use Cline\Keyphrase\Generators\PasswordGenerator;

describe('PasswordGenerator', function (): void {
    describe('create', function (): void {
        it('creates instance via static method', function (): void {
            $generator = PasswordGenerator::create();

            expect($generator)->toBeInstanceOf(PasswordGenerator::class);
        });

        it('allows fluent chaining from create', function (): void {
            $password = PasswordGenerator::create()
                ->length(20)
                ->withSymbols()
                ->generate();

            expect($password)->toHaveLength(20);
        });
    });

    describe('length', function (): void {
        it('generates password of specified length', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->length(20)->generate();

            expect($password)->toHaveLength(20);
        });

        it('throws exception for zero length', function (): void {
            $generator = new PasswordGenerator();
            $generator->length(0);
        })->throws(LengthMustBePositiveException::class);

        it('throws exception for negative length', function (): void {
            $generator = new PasswordGenerator();
            $generator->length(-5);
        })->throws(LengthMustBePositiveException::class);

        it('defaults to 16 characters', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->generate();

            expect($password)->toHaveLength(16);
        });
    });

    describe('character sets', function (): void {
        it('includes lowercase letters by default', function (): void {
            $generator = new PasswordGenerator();
            $generator = $generator->withUppercase(false)->withDigits(false);

            $password = $generator->length(100)->generate();

            expect($password)->toMatch('/^[a-z]+$/');
        });

        it('includes uppercase letters when enabled', function (): void {
            $generator = new PasswordGenerator();
            $generator = $generator->withLowercase(false)->withDigits(false);

            $password = $generator->length(100)->generate();

            expect($password)->toMatch('/^[A-Z]+$/');
        });

        it('includes digits when enabled', function (): void {
            $generator = new PasswordGenerator();
            $generator = $generator->withLowercase(false)->withUppercase(false);

            $password = $generator->length(100)->generate();

            expect($password)->toMatch('/^\d+$/');
        });

        it('includes symbols when enabled', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->withSymbols()->length(100)->generate();

            expect($password)->toMatch('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/');
        });

        it('excludes ambiguous characters when requested', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->excludeAmbiguous()->length(1_000)->generate();

            expect($password)->not->toMatch('/[0O1lI]/');
        });
    });

    describe('presets', function (): void {
        it('creates alphanumeric password', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->alphanumeric()->length(100)->generate();

            expect($password)->toMatch('/^[a-zA-Z0-9]+$/');
        });

        it('creates PIN with digits only', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->pin()->length(6)->generate();

            expect($password)->toMatch('/^\d{6}$/');
        });

        it('creates hex password', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->hex()->length(32)->generate();

            expect($password)->toMatch('/^[0-9a-f]{32}$/');
        });
    });

    describe('customization', function (): void {
        it('adds custom characters', function (): void {
            $generator = new PasswordGenerator();
            $generator = $generator
                ->withLowercase(false)
                ->withUppercase(false)
                ->withDigits(false)
                ->withCustomCharacters('ABC');
            $password = $generator->length(100)->generate();

            expect($password)->toMatch('/^[ABC]+$/');
        });

        it('excludes specified characters', function (): void {
            $generator = new PasswordGenerator();
            $password = $generator->exclude('aeiou')->length(1_000)->generate();

            expect($password)->not->toMatch('/[aeiou]/');
        });
    });

    describe('entropy', function (): void {
        it('calculates entropy correctly for alphanumeric', function (): void {
            $generator = new PasswordGenerator();
            $entropy = $generator->alphanumeric()->length(16)->entropy();

            // 62 characters (26+26+10), log2(62) ≈ 5.95, 16 * 5.95 ≈ 95.27
            expect($entropy)->toBeGreaterThan(95)
                ->toBeLessThan(96);
        });

        it('calculates higher entropy for longer passwords', function (): void {
            $generator = new PasswordGenerator();
            $entropy16 = $generator->length(16)->entropy();
            $entropy32 = $generator->length(32)->entropy();

            expect($entropy32)->toBeGreaterThan($entropy16);
        });
    });

    describe('generateMany', function (): void {
        it('generates multiple unique passwords', function (): void {
            $generator = new PasswordGenerator();
            $passwords = $generator->length(32)->generateMany(10);

            expect($passwords)->toHaveCount(10);
            expect(array_unique($passwords))->toHaveCount(10);
        });
    });

    describe('immutability', function (): void {
        it('returns new instance when configuring', function (): void {
            $generator1 = new PasswordGenerator();
            $generator2 = $generator1->length(32);

            expect($generator1)->not->toBe($generator2);
            expect($generator1->generate())->toHaveLength(16);
            expect($generator2->generate())->toHaveLength(32);
        });
    });
});
