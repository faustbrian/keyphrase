## Table of Contents

1. [Getting Started](#doc-docs-readme) (`docs/README.md`)
2. [Password Generation](#doc-docs-password-generation) (`docs/password-generation.md`)
3. [Passphrase Generation](#doc-docs-passphrase-generation) (`docs/passphrase-generation.md`)
4. [Mnemonic Generation](#doc-docs-mnemonic-generation) (`docs/mnemonic-generation.md`)
5. [Entropy Guide](#doc-docs-entropy) (`docs/entropy.md`)
<a id="doc-docs-readme"></a>

Welcome to Keyphrase, a modern PHP library for generating secure passwords, passphrases, and BIP39 mnemonics with an immutable, fluent API.

## Installation

Install Keyphrase via Composer:

```bash
composer require cline/keyphrase
```

## Quick Start

### Generate a Password

```php
use Cline\Keyphrase\Generators\PasswordGenerator;

$password = PasswordGenerator::create()
    ->length(20)
    ->withSymbols()
    ->generate();

// Example: "K9#mPx$2nQwR@5vLjH8s"
```

### Generate a Passphrase

```php
use Cline\Keyphrase\Generators\PassphraseGenerator;

$passphrase = PassphraseGenerator::create()
    ->words(6)
    ->titleCase()
    ->generate();

// Example: "Correct-Horse-Battery-Staple-Cloud-Mint"
```

### Generate a BIP39 Mnemonic

```php
use Cline\Keyphrase\Generators\MnemonicGenerator;

$mnemonic = MnemonicGenerator::create()
    ->words(12)
    ->english()
    ->generate();

// Example: "abandon ability able about above absent absorb abstract absurd abuse access accident"
```

## Using the Manager

For convenient access to all generators, use the `KeyphraseManager`:

```php
use Cline\Keyphrase\KeyphraseManager;

$manager = new KeyphraseManager();

// Quick generation methods
$password = $manager->quickPassword(16);
$securePassword = $manager->quickSecurePassword(24);
$passphrase = $manager->quickPassphrase(6);
$mnemonic = $manager->quickMnemonic(12);
$pin = $manager->pin(4);

// Access generators
$password = $manager->password()
    ->length(32)
    ->withSymbols()
    ->excludeAmbiguous()
    ->generate();
```

## Laravel Integration

If you're using Laravel, the package auto-registers a service provider and facade:

```php
use Cline\Keyphrase\Facades\Keyphrase;

// Using the facade
$password = Keyphrase::password()->length(20)->generate();
$passphrase = Keyphrase::passphrase()->words(5)->generate();
$mnemonic = Keyphrase::mnemonic()->words(24)->generate();

// Quick methods
$pin = Keyphrase::pin(6);
$password = Keyphrase::quickSecurePassword(32);
```

## Immutability

All generators are immutable. Each method returns a new instance with the updated configuration:

```php
$base = PasswordGenerator::create()->length(16);
$withSymbols = $base->withSymbols();
$withoutSymbols = $base->withSymbols(false);

// $base, $withSymbols, and $withoutSymbols are all different instances
// Original configuration is never modified
```

This makes generators safe to share and reuse:

```php
$secureDefaults = PasswordGenerator::create()
    ->length(24)
    ->withSymbols()
    ->excludeAmbiguous();

// Create variations without modifying the original
$password1 = $secureDefaults->generate();
$password2 = $secureDefaults->length(32)->generate();
$password3 = $secureDefaults->withSymbols(false)->generate();
```

## Entropy Calculation

All generators can calculate the entropy (randomness) of generated values:

```php
$entropy = PasswordGenerator::create()
    ->length(16)
    ->alphanumeric()
    ->entropy();

// Returns: 95.27 (bits of entropy)
```

See the [Entropy Guide](#doc-docs-entropy) for more details on security strength.

## Next Steps

- **[Password Generation](#doc-docs-password-generation)** - Detailed password options
- **[Passphrase Generation](#doc-docs-passphrase-generation)** - EFF diceware passphrases
- **[Mnemonic Generation](#doc-docs-mnemonic-generation)** - BIP39 compliance
- **[Entropy Guide](#doc-docs-entropy)** - Understanding password strength

<a id="doc-docs-password-generation"></a>

The `PasswordGenerator` creates secure random passwords with customizable character sets, lengths, and exclusions.

## Basic Usage

```php
use Cline\Keyphrase\Generators\PasswordGenerator;

// Using static factory method
$password = PasswordGenerator::create()->generate();

// Using constructor
$generator = new PasswordGenerator();
$password = $generator->generate();
```

## Setting Length

```php
// Default is 16 characters
$password = PasswordGenerator::create()->generate();

// Custom length
$password = PasswordGenerator::create()
    ->length(32)
    ->generate();
```

## Character Sets

### Default Character Sets

By default, passwords include:
- Lowercase letters (a-z)
- Uppercase letters (A-Z)
- Digits (0-9)

Symbols are **not** included by default.

```php
$generator = PasswordGenerator::create();

// Toggle character sets
$password = $generator
    ->withLowercase(true)    // Include a-z (default: true)
    ->withUppercase(true)    // Include A-Z (default: true)
    ->withDigits(true)       // Include 0-9 (default: true)
    ->withSymbols(true)      // Include !@#$%^&*... (default: false)
    ->generate();
```

### Disabling Character Sets

```php
// Uppercase only
$password = PasswordGenerator::create()
    ->withLowercase(false)
    ->withDigits(false)
    ->generate();

// Digits only
$password = PasswordGenerator::create()
    ->withLowercase(false)
    ->withUppercase(false)
    ->generate();
```

## Presets

Common configurations are available as presets:

### Alphanumeric

Letters and digits only (no symbols):

```php
$password = PasswordGenerator::create()
    ->alphanumeric()
    ->length(20)
    ->generate();

// Example: "Kj8mNpQ2xR5vLwY9sT3z"
```

### PIN

Digits only:

```php
$pin = PasswordGenerator::create()
    ->pin()
    ->length(6)
    ->generate();

// Example: "847291"
```

### Hexadecimal

Lowercase hex characters (0-9, a-f):

```php
$hex = PasswordGenerator::create()
    ->hex()
    ->length(32)
    ->generate();

// Example: "a1b2c3d4e5f67890abcdef1234567890"
```

## Customization

### Excluding Ambiguous Characters

Remove visually similar characters (0, O, 1, l, I):

```php
$password = PasswordGenerator::create()
    ->excludeAmbiguous()
    ->generate();

// Never contains: 0, O, 1, l, I
```

### Excluding Specific Characters

Remove any characters you want to exclude:

```php
$password = PasswordGenerator::create()
    ->exclude('aeiou')
    ->generate();

// Never contains vowels
```

### Adding Custom Characters

Add your own characters to the pool:

```php
$password = PasswordGenerator::create()
    ->withLowercase(false)
    ->withUppercase(false)
    ->withDigits(false)
    ->withCustomCharacters('ABC123!@#')
    ->length(10)
    ->generate();

// Only uses: A, B, C, 1, 2, 3, !, @, #
```

## Generating Multiple Passwords

```php
$passwords = PasswordGenerator::create()
    ->length(20)
    ->withSymbols()
    ->generateMany(10);

// Returns array of 10 unique passwords
```

## Entropy Calculation

Calculate the entropy (bits of randomness) for the current configuration:

```php
$generator = PasswordGenerator::create()
    ->length(16)
    ->alphanumeric();

$entropy = $generator->entropy();
// Returns: 95.27 bits

// More characters = more entropy
$entropy = PasswordGenerator::create()
    ->length(16)
    ->withSymbols()
    ->entropy();
// Returns: ~105 bits
```

## Full Example

```php
use Cline\Keyphrase\Generators\PasswordGenerator;

// Create a secure password generator with common settings
$generator = PasswordGenerator::create()
    ->length(24)
    ->withSymbols()
    ->excludeAmbiguous();

// Generate passwords
$password1 = $generator->generate();
$password2 = $generator->generate();

// Check entropy
$entropy = $generator->entropy();
echo "Password entropy: {$entropy} bits";

// Generate multiple
$passwords = $generator->generateMany(5);
```

## Security Recommendations

| Use Case | Minimum Length | Recommended Settings |
|----------|---------------|---------------------|
| PIN | 6 | `->pin()->length(6)` |
| Web Account | 16 | `->length(16)->alphanumeric()` |
| High Security | 24+ | `->length(24)->withSymbols()->excludeAmbiguous()` |
| API Key | 32+ | `->length(32)->alphanumeric()` |
| Master Password | 20+ | `->length(20)->withSymbols()` |

<a id="doc-docs-passphrase-generation"></a>

The `PassphraseGenerator` creates memorable yet secure passphrases using the EFF diceware wordlists.

## Basic Usage

```php
use Cline\Keyphrase\Generators\PassphraseGenerator;

// Using static factory method
$passphrase = PassphraseGenerator::create()->generate();

// Using constructor
$generator = new PassphraseGenerator();
$passphrase = $generator->generate();
```

## Word Count

```php
// Default is 6 words
$passphrase = PassphraseGenerator::create()->generate();

// Custom word count
$passphrase = PassphraseGenerator::create()
    ->words(8)
    ->generate();
```

Recommended word counts:
- **4 words**: ~51 bits entropy (basic security)
- **6 words**: ~77 bits entropy (standard security)
- **8 words**: ~103 bits entropy (high security)
- **10 words**: ~129 bits entropy (maximum security)

## Wordlists

Three EFF diceware wordlists are available:

### Large Wordlist (Default)

7,776 words, optimized for security:

```php
$passphrase = PassphraseGenerator::create()
    ->large()
    ->generate();

// Example: "correct-horse-battery-staple-cloud-mint"
```

### Short Wordlist

1,296 shorter, more memorable words:

```php
$passphrase = PassphraseGenerator::create()
    ->short()
    ->generate();

// Example: "oak-tip-red-sun-bay-ice"
```

### Unique Prefix Wordlist

1,296 words with unique 3-character prefixes (easier autocomplete):

```php
$passphrase = PassphraseGenerator::create()
    ->uniquePrefix()
    ->generate();

// Example: "abs-bea-cli-dry-elm-fig"
```

### Using Enum Directly

```php
use Cline\Keyphrase\Enums\EFFWordList;

$passphrase = PassphraseGenerator::create()
    ->useWordList(EFFWordList::Short)
    ->generate();
```

## Separators

### Default Separator

Hyphen is the default separator:

```php
$passphrase = PassphraseGenerator::create()->generate();
// Example: "word-word-word-word-word-word"
```

### Custom Separator

```php
$passphrase = PassphraseGenerator::create()
    ->separator('_')
    ->generate();
// Example: "word_word_word_word_word_word"

$passphrase = PassphraseGenerator::create()
    ->separator('.')
    ->generate();
// Example: "word.word.word.word.word.word"
```

### Space Separator

```php
$passphrase = PassphraseGenerator::create()
    ->withSpaces()
    ->generate();
// Example: "word word word word word word"
```

### No Separator

```php
$passphrase = PassphraseGenerator::create()
    ->noSeparator()
    ->generate();
// Example: "wordwordwordwordwordword"
```

## Case Transformations

### Title Case

```php
$passphrase = PassphraseGenerator::create()
    ->titleCase()
    ->generate();
// Example: "Correct-Horse-Battery-Staple-Cloud-Mint"
```

### Uppercase

```php
$passphrase = PassphraseGenerator::create()
    ->uppercase()
    ->generate();
// Example: "CORRECT-HORSE-BATTERY-STAPLE-CLOUD-MINT"
```

### Lowercase (Default)

```php
$passphrase = PassphraseGenerator::create()
    ->lowercase()
    ->generate();
// Example: "correct-horse-battery-staple-cloud-mint"
```

## Including Numbers

Add a random number to the passphrase for additional entropy:

```php
$passphrase = PassphraseGenerator::create()
    ->words(4)
    ->includeNumber()
    ->generate();
// Example: "correct-42-horse-battery-staple"
```

The number is inserted at a random position among the words.

## Generating Multiple Passphrases

```php
$passphrases = PassphraseGenerator::create()
    ->words(6)
    ->titleCase()
    ->generateMany(5);

// Returns array of 5 unique passphrases
```

## Entropy Calculation

```php
$generator = PassphraseGenerator::create()
    ->words(6)
    ->large();

$entropy = $generator->entropy();
// Returns: ~77.55 bits

// Including a number adds entropy
$entropy = PassphraseGenerator::create()
    ->words(6)
    ->includeNumber()
    ->entropy();
// Returns: ~81 bits
```

## Full Example

```php
use Cline\Keyphrase\Generators\PassphraseGenerator;

// Create a passphrase generator with common settings
$generator = PassphraseGenerator::create()
    ->words(6)
    ->large()
    ->titleCase()
    ->separator('-');

// Generate passphrases
$passphrase1 = $generator->generate();
$passphrase2 = $generator->generate();

// Check entropy
$entropy = $generator->entropy();
echo "Passphrase entropy: {$entropy} bits";

// Generate with number for extra security
$withNumber = $generator->includeNumber()->generate();
```

## Why Passphrases?

Passphrases offer several advantages over traditional passwords:

1. **Memorability**: Words are easier to remember than random characters
2. **Typing Speed**: Familiar words are faster to type
3. **Security**: Long passphrases have excellent entropy
4. **Resistance**: Harder to shoulder-surf than short passwords

### Comparison

| Type | Example | Entropy | Memorability |
|------|---------|---------|--------------|
| 8-char password | `K9#mPx$2` | ~52 bits | Hard |
| 6-word passphrase | `correct-horse-battery-staple-cloud-mint` | ~77 bits | Easy |
| 12-char password | `K9#mPx$2nQwR` | ~79 bits | Very Hard |

<a id="doc-docs-mnemonic-generation"></a>

The `MnemonicGenerator` creates BIP39-compliant mnemonic phrases for cryptocurrency wallets and secure key derivation.

## Basic Usage

```php
use Cline\Keyphrase\Generators\MnemonicGenerator;

// Using static factory method
$mnemonic = MnemonicGenerator::create()->generate();

// Using constructor
$generator = new MnemonicGenerator();
$mnemonic = $generator->generate();
```

## Word Count

BIP39 specifies valid word counts that correspond to specific entropy levels:

| Words | Entropy Bits | Checksum Bits | Total Bits |
|-------|-------------|---------------|------------|
| 12 | 128 | 4 | 132 |
| 15 | 160 | 5 | 165 |
| 18 | 192 | 6 | 198 |
| 21 | 224 | 7 | 231 |
| 24 | 256 | 8 | 264 |

```php
// Default is 12 words (128 bits entropy)
$mnemonic = MnemonicGenerator::create()->generate();

// 24 words for maximum security (256 bits entropy)
$mnemonic = MnemonicGenerator::create()
    ->words(24)
    ->generate();
```

Invalid word counts will throw an `InvalidWordCountException`:

```php
// Throws InvalidWordCountException
$mnemonic = MnemonicGenerator::create()->words(10)->generate();
```

## Languages

BIP39 defines wordlists for 9 languages:

### English (Default)

```php
$mnemonic = MnemonicGenerator::create()
    ->english()
    ->generate();
```

### Spanish

```php
$mnemonic = MnemonicGenerator::create()
    ->spanish()
    ->generate();
```

### French

```php
$mnemonic = MnemonicGenerator::create()
    ->french()
    ->generate();
```

### Italian

```php
$mnemonic = MnemonicGenerator::create()
    ->italian()
    ->generate();
```

### Japanese

Japanese uses ideographic space (U+3000) as the separator:

```php
$mnemonic = MnemonicGenerator::create()
    ->japanese()
    ->generate();

// Words separated by ideographic space: "\u{3000}"
```

### Korean

```php
$mnemonic = MnemonicGenerator::create()
    ->korean()
    ->generate();
```

### Czech

```php
$mnemonic = MnemonicGenerator::create()
    ->czech()
    ->generate();
```

### Chinese (Simplified)

```php
$mnemonic = MnemonicGenerator::create()
    ->chineseSimplified()
    ->generate();
```

### Chinese (Traditional)

```php
$mnemonic = MnemonicGenerator::create()
    ->chineseTraditional()
    ->generate();
```

### Using Enum Directly

```php
use Cline\Keyphrase\Enums\BIP39Language;

$mnemonic = MnemonicGenerator::create()
    ->useLanguage(BIP39Language::Spanish)
    ->generate();
```

## Custom Separator

Override the default separator (space for most languages):

```php
$mnemonic = MnemonicGenerator::create()
    ->separator('-')
    ->generate();

// Example: "abandon-ability-able-about-above-absent-absorb-abstract-absurd-abuse-access-accident"
```

## Entropy Methods

### Total Entropy (Including Checksum)

```php
$generator = MnemonicGenerator::create()->words(12);

$entropy = $generator->entropy();
// Returns: 132.0 (128 bits + 4 checksum bits)
```

### Raw Entropy Bits

```php
$generator = MnemonicGenerator::create()->words(12);

$bits = $generator->entropyBits();
// Returns: 128
```

## Generating Multiple Mnemonics

```php
$mnemonics = MnemonicGenerator::create()
    ->words(12)
    ->english()
    ->generateMany(5);

// Returns array of 5 unique mnemonics
```

## Accessing Generator State

```php
$generator = MnemonicGenerator::create()
    ->words(24)
    ->spanish();

$wordCount = $generator->getWordCount();
// Returns: 24

$language = $generator->getLanguage();
// Returns: BIP39Language::Spanish
```

## Full Example

```php
use Cline\Keyphrase\Generators\MnemonicGenerator;
use Cline\Keyphrase\Enums\BIP39Language;

// Create a mnemonic generator for a cryptocurrency wallet
$generator = MnemonicGenerator::create()
    ->words(24)
    ->english();

// Generate the recovery phrase
$mnemonic = $generator->generate();

// Check entropy
$entropy = $generator->entropy();
echo "Mnemonic entropy: {$entropy} bits";

// Verify word count
$words = explode(' ', $mnemonic);
echo "Word count: " . count($words);

// Generate for different language
$spanishMnemonic = $generator
    ->useLanguage(BIP39Language::Spanish)
    ->generate();
```

## BIP39 Compliance

All generated mnemonics are fully BIP39 compliant:

1. **Wordlist**: Uses official BIP39 wordlists (2048 words each)
2. **Entropy**: Cryptographically secure random entropy
3. **Checksum**: Proper SHA-256 checksum appended
4. **Validation**: Word counts match BIP39 specification

### Wordlist Properties

Each BIP39 wordlist has these properties:
- Exactly 2048 words
- Words are sorted for binary search
- First 4 characters uniquely identify each word
- No word is a prefix of another word

## Security Recommendations

| Use Case | Words | Entropy |
|----------|-------|---------|
| Standard wallet | 12 | 128 bits |
| High-value wallet | 24 | 256 bits |
| Cold storage | 24 | 256 bits |
| Hardware wallet | 24 | 256 bits |

For maximum security:
- Always use 24 words for significant cryptocurrency holdings
- Store the mnemonic securely (metal backup, safety deposit box)
- Never store digitally or share online
- Test recovery before depositing funds

<a id="doc-docs-entropy"></a>

Understanding entropy helps you make informed decisions about password and passphrase security.

## What is Entropy?

Entropy measures the randomness or unpredictability of a password. It's expressed in bits - higher bits mean more possible combinations and harder to crack.

```php
use Cline\Keyphrase\Support\Entropy;

// Calculate entropy for a password
$entropy = Entropy::forPassword(16, 62);
// 16 characters from 62-character set (a-z, A-Z, 0-9)
// Returns: ~95.27 bits
```

## Entropy Calculation

### Password Entropy

```
Entropy = Length × log₂(CharacterSetSize)
```

```php
use Cline\Keyphrase\Generators\PasswordGenerator;

// Alphanumeric (62 characters: a-z, A-Z, 0-9)
$entropy = PasswordGenerator::create()
    ->alphanumeric()
    ->length(16)
    ->entropy();
// Returns: 95.27 bits

// With symbols (~95 characters)
$entropy = PasswordGenerator::create()
    ->withSymbols()
    ->length(16)
    ->entropy();
// Returns: ~105 bits
```

### Passphrase Entropy

```
Entropy = WordCount × log₂(WordlistSize)
```

```php
use Cline\Keyphrase\Generators\PassphraseGenerator;

// Large wordlist (7776 words)
$entropy = PassphraseGenerator::create()
    ->large()
    ->words(6)
    ->entropy();
// Returns: 77.55 bits

// With number included
$entropy = PassphraseGenerator::create()
    ->large()
    ->words(6)
    ->includeNumber()
    ->entropy();
// Returns: ~81 bits
```

### Mnemonic Entropy

BIP39 mnemonics have fixed entropy based on word count:

```php
use Cline\Keyphrase\Generators\MnemonicGenerator;

$generator = MnemonicGenerator::create()->words(12);

// Total entropy (including checksum)
$totalEntropy = $generator->entropy();
// Returns: 132.0 bits

// Raw entropy (without checksum)
$rawBits = $generator->entropyBits();
// Returns: 128
```

## Strength Labels

```php
use Cline\Keyphrase\Support\Entropy;

$label = Entropy::strengthLabel(50);
// Returns: "reasonable"
```

| Entropy (bits) | Label | Crack Time (1B guesses/sec) |
|---------------|-------|----------------------------|
| < 28 | Very Weak | Instant |
| 28-35 | Weak | Minutes |
| 36-59 | Reasonable | Days to Years |
| 60-127 | Strong | Centuries |
| >= 128 | Very Strong | Heat death of universe |

## Time to Crack

Estimate cracking time at various attack speeds:

```php
use Cline\Keyphrase\Support\Entropy;

$result = Entropy::timeToCrack(80);
// Returns: ['seconds' => 1.9e+16, 'human' => 'centuries']
```

The calculation assumes:
- 1 billion (10^9) guesses per second
- Average case (50% of keyspace)

### Human-Readable Output

```php
$result = Entropy::timeToCrack(20);
// ['seconds' => 524.3, 'human' => 'minutes']

$result = Entropy::timeToCrack(40);
// ['seconds' => 5.5e+5, 'human' => 'days']

$result = Entropy::timeToCrack(60);
// ['seconds' => 5.8e+10, 'human' => 'centuries']

$result = Entropy::timeToCrack(100);
// ['seconds' => 6.3e+22, 'human' => 'centuries']
```

## Using the Manager

The `KeyphraseManager` provides convenient access to entropy utilities:

```php
use Cline\Keyphrase\KeyphraseManager;

$manager = new KeyphraseManager();

// Get strength label
$label = $manager->strengthLabel(80);
// Returns: "strong"

// Get time to crack
$result = $manager->timeToCrack(80);
// Returns: ['seconds' => ..., 'human' => 'centuries']
```

## Security Recommendations

### Minimum Entropy by Use Case

| Use Case | Minimum Entropy | Recommendation |
|----------|----------------|----------------|
| Low-value accounts | 36 bits | 8-char alphanumeric |
| Standard accounts | 60 bits | 12-char with symbols |
| Financial/Email | 80 bits | 16-char with symbols or 6-word passphrase |
| Cryptocurrency | 128 bits | 24-word mnemonic |
| Master passwords | 100+ bits | 8+ word passphrase or 20+ char password |

### Entropy Comparison

```php
use Cline\Keyphrase\Generators\PasswordGenerator;
use Cline\Keyphrase\Generators\PassphraseGenerator;
use Cline\Keyphrase\Generators\MnemonicGenerator;

// ~95 bits
PasswordGenerator::create()->length(16)->alphanumeric()->entropy();

// ~77 bits
PassphraseGenerator::create()->words(6)->large()->entropy();

// 132 bits (128 + checksum)
MnemonicGenerator::create()->words(12)->entropy();

// ~105 bits
PasswordGenerator::create()->length(16)->withSymbols()->entropy();

// ~103 bits
PassphraseGenerator::create()->words(8)->large()->entropy();

// 264 bits (256 + checksum)
MnemonicGenerator::create()->words(24)->entropy();
```

## Attack Scenarios

### Online Attacks

- Speed: 100-1000 guesses/second (rate limited)
- 40 bits entropy = centuries to crack
- Most services have lockouts after failed attempts

### Offline Attacks

- Speed: Billions of guesses/second (GPU clusters)
- 60+ bits entropy recommended
- Hashed passwords can be attacked in parallel

### Nation-State Attacks

- Speed: Trillions of guesses/second (specialized hardware)
- 80+ bits entropy recommended
- 128+ bits for long-term secrets

## Full Example

```php
use Cline\Keyphrase\Generators\PasswordGenerator;
use Cline\Keyphrase\Generators\PassphraseGenerator;
use Cline\Keyphrase\Support\Entropy;

// Compare options for a master password

$password = PasswordGenerator::create()
    ->length(20)
    ->withSymbols();

$passphrase = PassphraseGenerator::create()
    ->words(6)
    ->large();

echo "Password entropy: " . $password->entropy() . " bits\n";
echo "Passphrase entropy: " . $passphrase->entropy() . " bits\n";

echo "Password strength: " . Entropy::strengthLabel($password->entropy()) . "\n";
echo "Passphrase strength: " . Entropy::strengthLabel($passphrase->entropy()) . "\n";

$pwdCrack = Entropy::timeToCrack($password->entropy());
$ppCrack = Entropy::timeToCrack($passphrase->entropy());

echo "Password crack time: " . $pwdCrack['human'] . "\n";
echo "Passphrase crack time: " . $ppCrack['human'] . "\n";
```
