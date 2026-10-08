<?php

namespace Amarenkov\MutableContentScramble\Scramble\RuleTransformers;

use Dedoc\Scramble\Contracts\RuleTransformer;
use Dedoc\Scramble\Support\Generator\Combined\AnyOf;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\NullType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\Type;
use Dedoc\Scramble\Support\Generator\TypeTransformer;
use Dedoc\Scramble\Support\RuleTransforming\NormalizedRule;
use Dedoc\Scramble\Support\RuleTransforming\RuleTransformerContext;
use Dedoc\Scramble\Support\Type\ObjectType;

use Amarenkov\MutableContent\Rules\LovItemCode;

class LovItemCodeRule implements RuleTransformer
{
    // public
    public function __construct(
        protected TypeTransformer $openApiTransformer,
    ) {}

    public function shouldHandle(NormalizedRule $rule): bool
    {
        return $rule->is(LovItemCode::class);
    }

    public function toSchema(Type $previous, NormalizedRule $rule, RuleTransformerContext $context): Type
    {
        $lovCode = $rule->getRule()->lovCode;

        $items = [
            $this->openApiTransformer->transform(new ObjectType('mutable_lov:'.$lovCode)),
            new StringType()->setDescription(__('mutable-content-scramble::schema.unlisted_lov_item_code')),
        ];

        $target = $previous instanceof ArrayType ? $previous->items : $previous;

        if ($target->nullable) {
            $items[] = new NullType();
        }

        $type = new AnyOf()->setItems($items);

        if ($previous instanceof ArrayType) {
            $previous->items = $type;

            return $previous;
        }

        return $type;
    }
}
