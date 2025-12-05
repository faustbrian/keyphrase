<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Support\SecureRandom;

describe('SecureRandom', function (): void {
    describe('int', function (): void {
        it('generates integer within range', function (): void {
            for ($i = 0; $i < 100; ++$i) {
                $value = SecureRandom::int(5, 10);
                expect($value)->toBeGreaterThanOrEqual(5)
                    ->toBeLessThanOrEqual(10);
            }
        });

        it('can generate minimum value', function (): void {
            $foundMin = false;

            for ($i = 0; $i < 1_000; ++$i) {
                if (SecureRandom::int(0, 2) === 0) {
                    $foundMin = true;

                    break;
                }
            }

            expect($foundMin)->toBeTrue();
        });

        it('can generate maximum value', function (): void {
            $foundMax = false;

            for ($i = 0; $i < 1_000; ++$i) {
                if (SecureRandom::int(0, 2) === 2) {
                    $foundMax = true;

                    break;
                }
            }

            expect($foundMax)->toBeTrue();
        });
    });

    describe('bytes', function (): void {
        it('generates bytes of correct length', function (): void {
            $bytes = SecureRandom::bytes(32);
            expect(mb_strlen($bytes, '8bit'))->toBe(32);
        });

        it('generates different bytes each time', function (): void {
            $bytes1 = SecureRandom::bytes(32);
            $bytes2 = SecureRandom::bytes(32);

            expect($bytes1)->not->toBe($bytes2);
        });
    });

    describe('element', function (): void {
        it('returns element from array', function (): void {
            $array = ['a', 'b', 'c'];

            for ($i = 0; $i < 100; ++$i) {
                $element = SecureRandom::element($array);
                expect($array)->toContain($element);
            }
        });

        it('can return any element', function (): void {
            $array = ['a', 'b', 'c'];
            $found = [];

            for ($i = 0; $i < 1_000; ++$i) {
                $found[SecureRandom::element($array)] = true;
            }

            expect($found)->toHaveCount(3);
        });
    });

    describe('elements', function (): void {
        it('returns correct number of elements', function (): void {
            $array = ['a', 'b', 'c', 'd', 'e'];
            $elements = SecureRandom::elements($array, 3);

            expect($elements)->toHaveCount(3);
        });

        it('all elements come from source array', function (): void {
            $array = ['a', 'b', 'c', 'd', 'e'];
            $elements = SecureRandom::elements($array, 3);

            foreach ($elements as $element) {
                expect($array)->toContain($element);
            }
        });

        it('allows duplicates', function (): void {
            // Small array, many selections - duplicates are inevitable
            $array = ['a', 'b'];
            $elements = SecureRandom::elements($array, 100);

            expect($elements)->toHaveCount(100);
        });
    });

    describe('string', function (): void {
        it('generates string of correct length', function (): void {
            $string = SecureRandom::string('abc', 10);

            expect($string)->toHaveLength(10);
        });

        it('only uses provided characters', function (): void {
            $string = SecureRandom::string('abc', 100);

            expect($string)->toMatch('/^[abc]+$/');
        });

        it('can use any character', function (): void {
            $found = [];

            for ($i = 0; $i < 1_000; ++$i) {
                $char = SecureRandom::string('abc', 1);
                $found[$char] = true;
            }

            expect($found)->toHaveCount(3);
        });
    });
});
