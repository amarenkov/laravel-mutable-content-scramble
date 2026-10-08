<?php

namespace Amarenkov\MutableContentScramble\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use Orchestra\Testbench\TestCase as BaseTestCase;

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\ScrambleServiceProvider;

use Amarenkov\MutableContent\Database\Seeders\FieldsSeeder;
use Amarenkov\MutableContent\Database\Seeders\LovsSeeder;
use Amarenkov\MutableContent\Models\ModelWithFields;
use Amarenkov\MutableContent\MutableContentServiceProvider;

use Amarenkov\MutableContentScramble\MutableContentScrambleServiceProvider;

use Amarenkov\MutableContentScramble\Tests\Fixtures\FixturesServiceProvider;
use Amarenkov\MutableContentScramble\Tests\Fixtures\Http\RecordController;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected static bool $databaseReady = false;

    protected function getPackageProviders($app): array
    {
        return [
            ScrambleServiceProvider::class,
            MutableContentServiceProvider::class,
            MutableContentScrambleServiceProvider::class,
            FixturesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.locale', 'en');
        $app['config']->set('database.default', 'pgsql');
    }

    protected function defineRoutes($router): void
    {
        $router->post('api/records', [RecordController::class, 'store']);
    }

    protected function setUpTraits()
    {
        if (!static::$databaseReady) {
            $this->prepareDatabase();

            static::$databaseReady = true;
        }

        return parent::setUpTraits();
    }

    protected function setUp(): void
    {
        parent::setUp();

        ModelWithFields::flushFieldDefinitions();
    }

    protected function prepareDatabase(): void
    {
        foreach (['public', 'logs'] as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS {$schema} CASCADE");
        }

        DB::statement('CREATE SCHEMA public');

        Artisan::call('migrate', [
            '--path' => [
                realpath(__DIR__.'/../vendor/amarenkov/laravel-mutable-content/database/migrations'),
                realpath(__DIR__.'/Fixtures/database/migrations'),
            ],
            '--realpath' => true,
        ]);

        $this->seed(LovsSeeder::class);
        $this->seed(FieldsSeeder::class);

        ModelWithFields::flushFieldDefinitions();
    }

    protected function generateDocs(): array
    {
        return app(Generator::class)(Scramble::getGeneratorConfig(Scramble::DEFAULT_API));
    }

    protected function requestSchema(): array
    {
        return $this->generateDocs()['paths']['/records']['post']['requestBody']['content']['application/json']['schema'];
    }
}
