<?php

namespace Amarenkov\MutableContentScramble\Scramble\Support\OperationExtensions\ParameterExtractor;

use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\OperationExtensions\ParameterExtractor\ParameterExtractor;

use Dedoc\Scramble\Support\Generator\TypeTransformer;

use Amarenkov\MutableContentScramble\Attributes\MutableRequest;

class MutableContentExtractor implements ParameterExtractor
{
    public function __construct(
        protected TypeTransformer $openApiTransformer,
    ) {}

    public function handle(RouteInfo $routeInfo, array $parameterExtractionResults): array
    {
        $attributes = [];

        foreach ($routeInfo->reflectionAction()->getAttributes(MutableRequest::class) as $mutableRequestAttribute) {
            $mutableRequestAttribute = $mutableRequestAttribute->newInstance();

            if ($mutableRequestAttribute->prefix) {
                $attributes[$mutableRequestAttribute->prefix] = $mutableRequestAttribute;
            } else {
                $attributes[] = $mutableRequestAttribute;
            }
        };

        if ($attributes) {
            krsort($attributes);

            foreach ($parameterExtractionResults as $parameterExtractionResult) {
                foreach ($parameterExtractionResult->parameters as $parameter) {
                    if (!$parameter->description) {
                        foreach ($attributes as $attribute) {
                            if ($attribute->mayHaveParameter($parameter->name)) {
                                $field = $attribute->getField($parameter->name);

                                if ($field) {
                                    $parameter->description = $field->label;

                                    break;
                                }
                            }
                        }
                        
                    }
                }
            }
        }

        return $parameterExtractionResults;
    }
}