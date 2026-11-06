<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support;

use Illuminate\Support\Collection;

use Amarenkov\MutableContent\Domain\LovRegistry;

class InLovTransformer extends LovTransformer
{
    // static
    public static function make(string $lovCode): self
    {
        return new self($lovCode);
    }

    // protected
    protected function cases(): Collection
    {
        return collect(app(LovRegistry::class)->getLovItemsOptions($this->lovCode));
    }

    protected function description(): string
    {
        return (string)app(LovRegistry::class)->getLovLabel($this->lovCode);
    }

    // public
    public function __construct(
        private string $lovCode
    ) {}
}
