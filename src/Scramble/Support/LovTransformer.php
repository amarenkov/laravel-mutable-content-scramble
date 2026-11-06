<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\UnknownType;

/**
 * Enum schema of a list of codes with labels.
 */
abstract class LovTransformer
{
    // protected
    /**
     * @return Collection<string|int, string> code => label
     */
    abstract protected function cases(): Collection;

    abstract protected function description(): string;

    protected function addEnumCasesDescriptions(OpenApi\Type $schemaType, Collection $cases): void
    {
        $descriptions = $cases->mapWithKeys(fn ($item, $key) => [$key => trim(Str::replace("\n", ' ', $item))]);

        if (!$descriptions->some(fn ($description) => (bool)$description)) {
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

    protected function addEnumDescription(OpenApi\Type $schemaType): void
    {
        $description = trim(Str::replace("\n", ' ', $this->description()));

        if (!$description) {
            return;
        }

        $schemaType->setDescription($description."\n".$schemaType->description);
        $schemaType->setAttribute('description', $description);
    }

    // public
    public function transform(): OpenApi\Type
    {
        $cases = $this->cases();

        if ($cases->isEmpty()) {
            return new UnknownType;
        }

        $schemaType = new StringType;
        $schemaType->enum($cases->keys()->all());

        $this->addEnumCasesDescriptions($schemaType, $cases);
        $this->addEnumDescription($schemaType);

        $schemaType->setExtensionProperty('enumNames', $cases->values()->all());

        return $schemaType;
    }
}
