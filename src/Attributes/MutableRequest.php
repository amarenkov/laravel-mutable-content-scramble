<?php

namespace Amarenkov\MutableContentScramble\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class MutableRequest
{
    public function __construct(
        public readonly string $class,
        public readonly ?string $prefix = null
    ) {}

    public function mayHaveParameter($name)
    {
        if ($this->prefix === null) {
            return true;
        }

        return strpos($name, $this->prefix.'.') === 0;
    }

    public function getField($parameterName)
    {
        $fieldName = $this->prefix ? substr($parameterName, strlen($this->prefix) + 1) : $parameterName;

        $fieldDefinitions = ($this->class)::getFieldDefinitions();

        return @$fieldDefinitions[$fieldName];
    }
}
