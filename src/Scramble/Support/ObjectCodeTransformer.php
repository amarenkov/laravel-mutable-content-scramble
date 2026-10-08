<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support;

use Illuminate\Support\Str;

use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Generator\Types\StringType;

use Amarenkov\MutableContent\Helpers\ObjectHelper;

class ObjectCodeTransformer
{
    // const
    public const CODES_LIMIT = 200;

    public function __construct(
        private string $objectClass
    ) {}

    public static function make(string $objectClass): self
    {
        return new self($objectClass);
    }

    public function transform(): OpenApi\Type
    {
        $schemaType = new StringType;

        $classLabel = trim(Str::replace("\n", ' ', ObjectHelper::getClassLabel($this->objectClass)));

        $options = ObjectHelper::getCodeOptions($this->objectClass, self::CODES_LIMIT);

        if (!$options) {
            $schemaType->setDescription(__('mutable-content-scramble::schema.object_code', ['class' => $classLabel]));

            return $schemaType;
        }

        $schemaType->enum(array_map('strval', array_keys($options)));

        $casesDescription = collect($options)
            ->map(fn ($title, $code) => "| `{$code}` <br/> ".trim(Str::replace("\n", ' ', $title)).' |')
            ->prepend('|---|')
            ->prepend('| |')
            ->join("\n");

        $schemaType->setDescription($classLabel."\n".$casesDescription);
        $schemaType->setAttribute('casesDescription', $casesDescription);
        $schemaType->setAttribute('description', $classLabel);

        $schemaType->setExtensionProperty('enumNames', array_values($options));

        return $schemaType;
    }
}
