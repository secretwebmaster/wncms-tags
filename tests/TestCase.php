<?php

namespace Wncms\Tags\Tests;

use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Wncms\Tags\TagsServiceProvider;
use Wncms\Translatable\TranslatableServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            TagsServiceProvider::class,
            TranslatableServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config): void {
            $config->set('app.locale', 'en');
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
            $config->set('queue.batching.database', 'testbench');
            $config->set('queue.failed.database', 'testbench');
            $config->set('translatable.create_translation_for_default_locale', true);
            $config->set('wncms-tags.is_translatable', true);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../../wncms-translatable/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
