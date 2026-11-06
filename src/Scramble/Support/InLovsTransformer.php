<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support;

use Illuminate\Support\Collection;

use Amarenkov\MutableContent\Domain\LovRegistry;

class InLovsTransformer extends LovTransformer
{
    // static
    public static function make(): self
    {
        return new self();
    }

    // protected
    protected function cases(): Collection
    {
        return collect(app(LovRegistry::class)->getLovsOptions());
    }

    protected function description(): string
    {
        return __('mutable-content-scramble::schema.lovs');
    }
}
