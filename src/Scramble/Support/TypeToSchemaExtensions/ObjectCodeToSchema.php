<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support\TypeToSchemaExtensions;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;

use Amarenkov\MutableContentScramble\Scramble\Support\ObjectCodeTransformer;

class ObjectCodeToSchema extends TypeToSchemaExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && strpos($type->name, 'mutable_object:') === 0;
    }

    /**
     * @param  ObjectType  $type
     */
    public function toSchema(Type $type): OpenApi\Type
    {
        list(,$objectClass) = explode(':', $type->name, 2);

        return ObjectCodeTransformer::make($objectClass)->transform();
    }

    public function reference(ObjectType $type): Reference
    {
        list(,$objectClass) = explode(':', $type->name, 2);

        return new Reference('schemas', $type->name, $this->components, str_replace('\\', '.', $objectClass));
    }
}
