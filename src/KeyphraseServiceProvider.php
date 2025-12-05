<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Keyphrase;

use Override;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Laravel service provider for the Keyphrase package.
 *
 * Registers the KeyphraseManager as a singleton in the Laravel service container
 * and provides the 'keyphrase' alias for convenient resolution.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class KeyphraseServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     *
     * Sets the package name for Laravel Package Tools integration.
     *
     * @param Package $package The package configuration instance
     */
    #[Override()]
    public function configurePackage(Package $package): void
    {
        $package->name('keyphrase');
    }

    /**
     * Register package services.
     *
     * Binds KeyphraseManager as a singleton in the service container and
     * creates an alias 'keyphrase' for facade-style access and dependency injection.
     */
    #[Override()]
    public function registeringPackage(): void
    {
        $this->app->singleton(KeyphraseManager::class);
        $this->app->alias(KeyphraseManager::class, 'keyphrase');
    }
}
