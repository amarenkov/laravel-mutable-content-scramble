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

use Amarenkov\MutableContent\Helpers\ObjectHelper;
use Amarenkov\MutableContent\Rules\ObjectCode;
use Amarenkov\MutableContent\Rules\ObjectCodeExists;

class ObjectCodeRule implements RuleTransformer
{
    // private
    private function preservePreviousRules(Type $current, Type $previous): Type
    {
        if ($previous->nullable) {
            $current->nullable(true);
        }

        return $current;
    }

    // public
    public function __construct(
        protected TypeTransformer $openApiTransformer,
    ) {}

    public function shouldHandle(NormalizedRule $rule): bool
    {
        return $rule->is(ObjectCodeExists::class) || $rule->is(ObjectCode::class);
    }

    public function toSchema(Type $previous, NormalizedRule $rule, RuleTransformerContext $context): Type
    {
        $objectRule = $rule->getRule();

        $objectType = $this->openApiTransformer->transform(new ObjectType('mutable_object:'.$objectRule->objectClass));

        $target = $previous instanceof ArrayType ? $previous->items : $previous;

        if ($objectRule instanceof ObjectCode) {
            $items = [
                $objectType,
                new StringType()->setDescription(__('mutable-content-scramble::schema.unlisted_object_code', ['class' => ObjectHelper::getClassLabel($objectRule->objectClass)])),
            ];

            if ($target->nullable) {
                $items[] = new NullType();
            }

            $type = new AnyOf()->setItems($items);
        } else {
            $type = $this->preservePreviousRules($objectType, $target);
        }

        if ($previous instanceof ArrayType) {
            $previous->items = $type;

            return $previous;
        }

        return $type;
    }
}
