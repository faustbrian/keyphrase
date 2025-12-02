<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Enums\EFFWordList;
use Cline\Keyphrase\Exceptions\WordCountTooFewException;
use Cline\Keyphrase\Generators\PassphraseGenerator;

describe('PassphraseGenerator', function (): void {
    describe('create', function (): void {
        it('creates instance via static method', function (): void {
            $generator = PassphraseGenerator::create();

            expect($generator)->toBeInstanceOf(PassphraseGenerator::class);
        });

        it('allows fluent chaining from create', function (): void {
            $passphrase = PassphraseGenerator::create()
                ->words(4)
                ->titleCase()
                ->generate();

            $words = explode('-', $passphrase);
            expect($words)->toHaveCount(4);
        });
    });

    describe('word count', function (): void {
        it('generates passphrase with specified word count', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(6)->generate();
            $words = explode('-', $passphrase);

            expect($words)->toHaveCount(6);
        });

        it('throws exception for zero word count', function (): void {
            $generator = new PassphraseGenerator();
            $generator->words(0);
        })->throws(WordCountTooFewException::class);

        it('throws exception for negative word count', function (): void {
            $generator = new PassphraseGenerator();
            $generator->words(-1);
        })->throws(WordCountTooFewException::class);

        it('defaults to 6 words', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->generate();
            $words = explode('-', $passphrase);

            expect($words)->toHaveCount(6);
        });
    });

    describe('wordlists', function (): void {
        it('uses large wordlist by default', function (): void {
            $generator = new PassphraseGenerator();
            // Large wordlist has longer words on average
            $passphrase = $generator->generate();

            expect($passphrase)->not->toBeEmpty();
        });

        it('uses short wordlist when specified', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->short()->generate();

            expect($passphrase)->not->toBeEmpty();
        });

        it('uses unique prefix wordlist when specified', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->uniquePrefix()->generate();

            expect($passphrase)->not->toBeEmpty();
        });

        it('accepts wordlist enum directly', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->useWordList(EFFWordList::Large)->generate();

            expect($passphrase)->not->toBeEmpty();
        });
    });

    describe('separators', function (): void {
        it('uses hyphen separator by default', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->generate();

            expect($passphrase)->toContain('-');
        });

        it('uses custom separator', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->separator('_')->generate();

            expect($passphrase)->toContain('_');
            expect($passphrase)->not->toContain('-');
        });

        it('uses space separator', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->withSpaces()->generate();

            expect($passphrase)->toContain(' ');
        });

        it('uses no separator', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->noSeparator()->generate();

            expect($passphrase)->not->toContain('-');
            expect($passphrase)->not->toContain(' ');
        });
    });

    describe('case transformations', function (): void {
        it('applies title case', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->titleCase()->generate();
            $words = explode('-', $passphrase);

            foreach ($words as $word) {
                expect(ucfirst($word))->toBe($word);
            }
        });

        it('applies uppercase', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->uppercase()->generate();

            expect($passphrase)->toBe(mb_strtoupper($passphrase));
        });

        it('applies lowercase', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->lowercase()->generate();

            expect($passphrase)->toBe(mb_strtolower($passphrase));
        });
    });

    describe('number inclusion', function (): void {
        it('includes number when requested', function (): void {
            $generator = new PassphraseGenerator();
            $passphrase = $generator->words(3)->includeNumber()->generate();

            expect($passphrase)->toMatch('/\d/');
        });

        it('number appears at random position', function (): void {
            $generator = new PassphraseGenerator();
            $positions = [];

            for ($i = 0; $i < 100; ++$i) {
                $passphrase = $generator->words(3)->includeNumber()->generate();
                $parts = explode('-', $passphrase);

                foreach ($parts as $index => $part) {
                    if (preg_match('/^\d+$/', $part)) {
                        $positions[] = $index;

                        break;
                    }
                }
            }

            // Should have some variation in positions
            expect(count(array_unique($positions)))->toBeGreaterThan(1);
        });
    });

    describe('entropy', function (): void {
        it('calculates entropy for large wordlist', function (): void {
            $generator = new PassphraseGenerator();
            $entropy = $generator->large()->words(6)->entropy();

            // 7776 words, log2(7776) ≈ 12.925, 6 * 12.925 ≈ 77.55
            expect($entropy)->toBeGreaterThan(77)
                ->toBeLessThan(78);
        });

        it('calculates higher entropy for more words', function (): void {
            $generator = new PassphraseGenerator();
            $entropy6 = $generator->words(6)->entropy();
            $entropy8 = $generator->words(8)->entropy();

            expect($entropy8)->toBeGreaterThan($entropy6);
        });

        it('includes number entropy when enabled', function (): void {
            $generator = new PassphraseGenerator();
            $entropyWithout = $generator->words(6)->entropy();
            $entropyWith = $generator->words(6)->includeNumber()->entropy();

            expect($entropyWith)->toBeGreaterThan($entropyWithout);
        });
    });

    describe('generateMany', function (): void {
        it('generates multiple passphrases', function (): void {
            $generator = new PassphraseGenerator();
            $passphrases = $generator->words(4)->generateMany(5);

            expect($passphrases)->toHaveCount(5);
        });
    });

    describe('immutability', function (): void {
        it('returns new instance when configuring', function (): void {
            $generator1 = new PassphraseGenerator();
            $generator2 = $generator1->words(8);

            expect($generator1)->not->toBe($generator2);

            $words1 = count(explode('-', $generator1->generate()));
            $words2 = count(explode('-', $generator2->generate()));

            expect($words1)->toBe(6);
            expect($words2)->toBe(8);
        });
    });
});
