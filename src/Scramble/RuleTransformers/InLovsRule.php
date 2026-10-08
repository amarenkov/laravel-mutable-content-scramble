<?php

namespace Amarenkov\MutableContentScramble\Scramble\RuleTransformers;

use Dedoc\Scramble\Contracts\RuleTransformer;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\Type;
use Dedoc\Scramble\Support\Generator\TypeTransformer;
use Dedoc\Scramble\Support\RuleTransforming\NormalizedRule;
use Dedoc\Scramble\Support\RuleTransforming\RuleTransformerContext;
use Dedoc\Scramble\Support\Type\ObjectType;

use Amarenkov\MutableContent\Rules\InLovs;

class InLovsRule implements RuleTransformer
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
        return $rule->is(InLovs::class);
    }

    public function toSchema(Type $previous, NormalizedRule $rule, RuleTransformerContext $context): Type
    {
        $rule = $rule->getRule();

        $lovsType = $this->openApiTransformer->transform(new ObjectType('mutable_lovs'));

        if ($previous instanceof ArrayType) {
            $previous->items = $this->preservePreviousRules($lovsType, $previous->items);

            return $previous;
        }

        return $this->preservePreviousRules($lovsType, $previous);
    }
}
