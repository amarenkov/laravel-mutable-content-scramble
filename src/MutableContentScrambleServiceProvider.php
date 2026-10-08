<?php

namespace Amarenkov\MutableContentScramble;

use Illuminate\Support\ServiceProvider;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Configuration\ParametersExtractors;

use Amarenkov\MutableContentScramble\Scramble\Support\OperationExtensions\ParameterExtractor\MutableContentExtractor;
use Amarenkov\MutableContentScramble\Scramble\Support\TypeToSchemaExtensions\InLovsToSchema;
use Amarenkov\MutableContentScramble\Scramble\Support\TypeToSchemaExtensions\InLovToSchema;
use Amarenkov\MutableContentScramble\Scramble\Support\TypeToSchemaExtensions\ObjectCodeToSchema;

use Amarenkov\MutableContentScramble\Scramble\RuleTransformers\InLovRule;
use Amarenkov\MutableContentScramble\Scramble\RuleTransformers\InLovsRule;
use Amarenkov\MutableContentScramble\Scramble\RuleTransformers\LovItemCodeRule;
use Amarenkov\MutableContentScramble\Scramble\RuleTransformers\ObjectCodeRule;

class MutableContentScrambleServiceProvider extends ServiceProvider
{
    // protected

    // public
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/mutable-content-scramble'),
        ], 'mutable-content-scramble-public');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mutable-content-scramble');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mutable-content-scramble');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/mutable-content-scramble'),
        ], 'mutable-content-scramble-lang');

        Scramble::configure()
            ->withParametersExtractors(function (ParametersExtractors $extractors) {
                $extractors->append(MutableContentExtractor::class);
            })
            ->withRuleTransformers([
                InLovsRule::class,
                InLovRule::class,
                LovItemCodeRule::class,
                ObjectCodeRule::class,
            ]);

        Scramble::registerExtensions([
            InLovsToSchema::class,
            InLovToSchema::class,
            ObjectCodeToSchema::class,
        ]);
    }
}