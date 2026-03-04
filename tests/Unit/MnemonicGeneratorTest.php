<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Enums\BIP39Language;
use Cline\Keyphrase\Exceptions\WordCountNotInAllowedSetException;
use Cline\Keyphrase\Generators\MnemonicGenerator;

describe('MnemonicGenerator', function (): void {
    describe('create', function (): void {
        it('creates instance via static method', function (): void {
            $generator = MnemonicGenerator::create();

            expect($generator)->toBeInstanceOf(MnemonicGenerator::class);
        });

        it('allows fluent chaining from create', function (): void {
            $mnemonic = MnemonicGenerator::create()
                ->words(24)
                ->english()
                ->generate();

            $words = explode(' ', $mnemonic);
            expect($words)->toHaveCount(24);
        });
    });

    describe('word count', function (): void {
        it('generates 12-word mnemonic by default', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->generate();
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(12);
        });

        it('generates 15-word mnemonic', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->words(15)->generate();
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(15);
        });

        it('generates 18-word mnemonic', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->words(18)->generate();
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(18);
        });

        it('generates 21-word mnemonic', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->words(21)->generate();
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(21);
        });

        it('generates 24-word mnemonic', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->words(24)->generate();
            $words = explode(' ', $mnemonic);

            expect($words)->toHaveCount(24);
        });

        it('throws exception for invalid word count', function (): void {
            $generator = new MnemonicGenerator();
            $generator->words(10);
        })->throws(WordCountNotInAllowedSetException::class);

        it('throws exception for word count 13', function (): void {
            $generator = new MnemonicGenerator();
            $generator->words(13);
        })->throws(WordCountNotInAllowedSetException::class);
    });

    describe('languages', function (): void {
        it('uses English by default', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->getLanguage())->toBe(BIP39Language::English);
        });

        it('supports English', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->english()->generate();

            expect($mnemonic)->toMatch('/^[a-z\s]+$/');
        });

        it('supports Spanish', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->spanish()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('supports French', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->french()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('supports Italian', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->italian()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('supports Japanese with ideographic separator', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->japanese()->generate();

            // Japanese uses ideographic space (U+3000) as separator
            expect($mnemonic)->toContain("\u{3000}");
        });

        it('supports Korean', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->korean()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('supports Czech', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->czech()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('supports Simplified Chinese', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->chineseSimplified()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('supports Traditional Chinese', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->chineseTraditional()->generate();

            expect($mnemonic)->not->toBeEmpty();
        });

        it('accepts language enum directly', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->useLanguage(BIP39Language::Spanish)->generate();

            expect($mnemonic)->not->toBeEmpty();
        });
    });

    describe('separator', function (): void {
        it('uses space separator by default for English', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->english()->generate();

            expect($mnemonic)->toContain(' ');
        });

        it('allows custom separator', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->separator('-')->generate();

            expect($mnemonic)->toContain('-');
            expect($mnemonic)->not->toContain(' ');
        });
    });

    describe('entropy', function (): void {
        it('calculates 132 bits for 12 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(12)->entropy())->toBe(132.0);
        });

        it('calculates 165 bits for 15 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(15)->entropy())->toBe(165.0);
        });

        it('calculates 198 bits for 18 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(18)->entropy())->toBe(198.0);
        });

        it('calculates 231 bits for 21 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(21)->entropy())->toBe(231.0);
        });

        it('calculates 264 bits for 24 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(24)->entropy())->toBe(264.0);
        });
    });

    describe('entropy bits (raw)', function (): void {
        it('returns 128 bits for 12 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(12)->entropyBits())->toBe(128);
        });

        it('returns 256 bits for 24 words', function (): void {
            $generator = new MnemonicGenerator();

            expect($generator->words(24)->entropyBits())->toBe(256);
        });
    });

    describe('generateMany', function (): void {
        it('generates multiple mnemonics', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonics = $generator->generateMany(5);

            expect($mnemonics)->toHaveCount(5);
        });

        it('generates unique mnemonics', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonics = $generator->words(24)->generateMany(10);

            expect(array_unique($mnemonics))->toHaveCount(10);
        });
    });

    describe('immutability', function (): void {
        it('returns new instance when configuring', function (): void {
            $generator1 = new MnemonicGenerator();
            $generator2 = $generator1->words(24);

            expect($generator1)->not->toBe($generator2);
            expect($generator1->getWordCount())->toBe(12);
            expect($generator2->getWordCount())->toBe(24);
        });
    });

    describe('BIP39 compliance', function (): void {
        it('generates words from BIP39 wordlist', function (): void {
            $generator = new MnemonicGenerator();
            $mnemonic = $generator->english()->words(12)->generate();
            $words = explode(' ', $mnemonic);

            // Load the wordlist and verify each word exists
            $wordlistPath = __DIR__.'/../../src/WordLists/BIP39/english.txt';
            $wordlist = array_filter(explode("\n", file_get_contents($wordlistPath)));

            foreach ($words as $word) {
                expect($wordlist)->toContain($word);
            }
        });

        it('uses exactly 2048 words in wordlist', function (): void {
            $wordlistPath = __DIR__.'/../../src/WordLists/BIP39/english.txt';
            $wordlist = array_filter(explode("\n", file_get_contents($wordlistPath)));

            expect($wordlist)->toHaveCount(2_048);
        });
    });
});
