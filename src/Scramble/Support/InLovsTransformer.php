<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\UnknownType;

use Amarenkov\MutableContent\Domain\LovRegistry;

class InLovsTransformer
{
    private array $except = [];

    private array $only = [];

    public function __construct(
    ) {}

    public static function make(): self
    {
        return new self();
    }

    public function except(array $except): self
    {
        $this->except = $except;

        return $this;
    }

    public function only(array $only): self
    {
        $this->only = $only;

        return $this;
    }

    public function transform(): OpenApi\Type
    {
        $cases = $this->cases();

        if ($cases->isEmpty()) {
            return new UnknownType;
        }

        $values = $cases->keys()->all();

        $schemaType = new StringType;

        if (count($values) === 1 && ($this->only || $this->except)) {
            $schemaType->const($values[0]);
        } else {
            $schemaType->enum($values);
        }

        $this->addEnumCasesDescriptions($schemaType, $cases);

        $this->addEnumDescription($schemaType);

        $this->addEnumNames($schemaType, $cases);

        return $schemaType;
    }

    private function cases(): Collection
    {
        $lovRegistry = app(LovRegistry::class);
        
        return collect($lovRegistry->getLovsOptions())
            ->reject(fn ($case) => in_array($case, $this->except))
            ->filter(fn ($case) => ! $this->only || in_array($case, $this->only));
    }

    private function addEnumCasesDescriptions(OpenApi\Type $schemaType, Collection $cases): void
    {
        $descriptions = $cases
            ->mapWithKeys(function ($item, $key) {
                return [
                    $key => trim(Str::replace("\n", ' ', $item)),
                ];
            });

        if (! $descriptions->some(fn ($description) => (bool) $description)) {
            return;
        }

        $description = $descriptions
            ->map(fn ($description, $value) => "| `{$value}` <br/> {$description} |")
            ->prepend('|---|')
            ->prepend('| |')
            ->join("\n");

        $schemaType->setDescription($description);

        $schemaType->setAttribute('casesDescription', $description);
    }

    private function addEnumDescription(OpenApi\Type $schemaType): void
    {
        $description = trim(Str::replace("\n", ' ', __('mutable-content-scramble::schema.lovs'))); // @phpstan-ignore binaryOp.invalid, binaryOp.invalid

        if (! $description) {
            return;
        }

        $schemaType->setDescription($description."\n".$schemaType->description);

        $schemaType->setAttribute('description', $description);
    }

    private function addEnumNames(OpenApi\Type $schemaType, Collection $cases): void
    {
        $schemaType->setExtensionProperty(
            'enumNames',
            $cases->map(fn ($case) => $case)->values()->all(),
        );
    }
}
