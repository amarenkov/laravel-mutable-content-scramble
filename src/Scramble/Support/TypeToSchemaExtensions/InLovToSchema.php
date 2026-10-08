<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support\TypeToSchemaExtensions;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;

use Amarenkov\MutableContentScramble\Scramble\Support\InLovTransformer;

class InLovToSchema extends TypeToSchemaExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && strpos($type->name, 'mutable_lov:') === 0;
    }

    /**
     * @param  ObjectType  $type
     */
    public function toSchema(Type $type): OpenApi\Type
    {
        list(,$lovCode) = explode(':', $type->name, 2);

        return InLovTransformer::make($lovCode)->transform();
    }

    public function reference(ObjectType $type): Reference
    {
        list(,$lovCode) = explode(':', $type->name, 2);
        
        return new Reference('schemas', $type->name, $this->components, $lovCode);
    }
}
