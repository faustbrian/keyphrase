<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Keyphrase\Exceptions\InvalidLengthException;
use Cline\Keyphrase\Exceptions\InvalidWordCountException;
use Cline\Keyphrase\Exceptions\KeyphraseException;
use Cline\Keyphrase\Exceptions\LengthMustBePositiveException;
use Cline\Keyphrase\Exceptions\LengthTooShortException;
use Cline\Keyphrase\Exceptions\WordCountNotInAllowedSetException;
use Cline\Keyphrase\Exceptions\WordCountTooFewException;
use Cline\Keyphrase\Exceptions\WordListNotFoundException;

describe('KeyphraseException marker interface', function (): void {
    it('is implemented by all exception classes', function (): void {
        expect(LengthTooShortException::forLength(5, 10))->toBeInstanceOf(KeyphraseException::class);
        expect(LengthMustBePositiveException::forLength(-5))->toBeInstanceOf(KeyphraseException::class);
        expect(WordCountTooFewException::forCount(2, 4))->toBeInstanceOf(KeyphraseException::class);
        expect(WordCountNotInAllowedSetException::forCount(10, [12, 15, 18, 21, 24]))->toBeInstanceOf(KeyphraseException::class);
        expect(WordListNotFoundException::forPath('/invalid/path.txt'))->toBeInstanceOf(KeyphraseException::class);
    });
});

describe('LengthTooShortException', function (): void {
    it('creates exception with correct message', function (): void {
        $exception = LengthTooShortException::forLength(5, 10);

        expect($exception)->toBeInstanceOf(LengthTooShortException::class);
        expect($exception)->toBeInstanceOf(InvalidLengthException::class);
        expect($exception->getMessage())->toBe('Length must be at least 10, got 5.');
    });
});

describe('LengthMustBePositiveException', function (): void {
    it('creates exception with correct message', function (): void {
        $exception = LengthMustBePositiveException::forLength(-5);

        expect($exception)->toBeInstanceOf(LengthMustBePositiveException::class);
        expect($exception)->toBeInstanceOf(InvalidLengthException::class);
        expect($exception->getMessage())->toBe('Length must be a positive integer, got -5.');
    });
});

describe('WordCountNotInAllowedSetException', function (): void {
    it('creates exception with correct message', function (): void {
        $exception = WordCountNotInAllowedSetException::forCount(10, [12, 15, 18, 21, 24]);

        expect($exception)->toBeInstanceOf(WordCountNotInAllowedSetException::class);
        expect($exception)->toBeInstanceOf(InvalidWordCountException::class);
        expect($exception->getMessage())->toContain('10');
        expect($exception->getMessage())->toContain('12, 15, 18, 21, 24');
    });
});

describe('WordCountTooFewException', function (): void {
    it('creates exception with correct message', function (): void {
        $exception = WordCountTooFewException::forCount(2, 4);

        expect($exception)->toBeInstanceOf(WordCountTooFewException::class);
        expect($exception)->toBeInstanceOf(InvalidWordCountException::class);
        expect($exception->getMessage())->toBe('Word count must be at least 4, got 2.');
    });
});

describe('WordListNotFoundException', function (): void {
    it('creates forPath exception with correct message', function (): void {
        $exception = WordListNotFoundException::forPath('/invalid/path.txt');

        expect($exception)->toBeInstanceOf(WordListNotFoundException::class);
        expect($exception->getMessage())->toBe('Wordlist file not found or unreadable: /invalid/path.txt');
    });
});
