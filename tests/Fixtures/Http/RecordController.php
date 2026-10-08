<?php

namespace Amarenkov\MutableContentScramble\Tests\Fixtures\Http;

use Illuminate\Http\Request;

use Amarenkov\MutableContent\Helpers\RuleHelper;

use Amarenkov\MutableContentScramble\Attributes\MutableRequest;

use Amarenkov\MutableContentScramble\Tests\Fixtures\Models\Record;

class RecordController
{
    #[MutableRequest(Record::class)]
    #[MutableRequest(Record::class, 'children.*')]
    public function store(Request $request)
    {
        $request->validate(
            RuleHelper::getValidationRules(Record::class) +
            ['children' => 'array'] +
            RuleHelper::getValidationRules(Record::class, 'children.*')
        );

        return response()->noContent();
    }
}
