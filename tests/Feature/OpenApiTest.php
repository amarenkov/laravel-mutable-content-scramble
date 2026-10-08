<?php

namespace Amarenkov\MutableContentScramble\Tests\Feature;

use Amarenkov\MutableContentScramble\Scramble\Support\ObjectCodeTransformer;

use Amarenkov\MutableContentScramble\Tests\TestCase;
use Amarenkov\MutableContentScramble\Tests\Fixtures\Models\Owner;

class OpenApiTest extends TestCase
{
    protected const OWNER_SCHEMA = 'Amarenkov.MutableContentScramble.Tests.Fixtures.Models.Owner';

    protected function makeOwners(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $owner = new Owner();
            $owner->fill(['code' => 'OWN-'.$i, 'label' => 'Owner '.$i]);
            $owner->save();
        }
    }

    protected function refOf(array $property): ?string
    {
        foreach ($property['anyOf'] ?? [$property] as $variant) {
            if (isset($variant['$ref'])) {
                return substr($variant['$ref'], strlen('#/components/schemas/'));
            }
        }

        return null;
    }

    public function test_parameters_are_described_by_field_labels(): void
    {
        $schema = $this->requestSchema();

        $this->assertSame('Code', $schema['properties']['code']['description']);
        $this->assertEquals(50, $schema['properties']['code']['maxLength']);
        $this->assertSame('Quantity', $schema['properties']['quantity']['description']);
        $this->assertSame('Quantity', $schema['properties']['children']['items']['properties']['quantity']['description']);
        $this->assertContains('quantity', $schema['required']);
    }

    public function test_lov_item_is_an_enum_with_labels(): void
    {
        $docs = $this->generateDocs();
        $status = $docs['paths']['/records']['post']['requestBody']['content']['application/json']['schema']['properties']['status'];

        $this->assertSame('record_status', $this->refOf($status));

        $component = $docs['components']['schemas']['record_status'];

        $this->assertSame(['active', 'draft'], $component['enum']);
        $this->assertSame(['Active', 'Draft'], $component['x-enumNames']);
        $this->assertStringContainsString('| `draft` <br/> Draft |', $component['description']);
    }

    public function test_unlisted_lov_codes_allow_any_string(): void
    {
        $tag = $this->requestSchema()['properties']['tag'];

        $this->assertSame('Tag', $tag['description']);
        $this->assertSame('record_status', $this->refOf($tag));
        $this->assertContains(['type' => 'string', 'description' => 'Code not listed in the LOV'], $tag['anyOf']);
        $this->assertContains(['type' => 'null'], $tag['anyOf']);
    }

    public function test_lov_field_lists_lovs(): void
    {
        $docs = $this->generateDocs();

        $this->assertSame('lovs', $this->refOf($docs['paths']['/records']['post']['requestBody']['content']['application/json']['schema']['properties']['source_lov']));
        $this->assertContains('record_status', $docs['components']['schemas']['lovs']['enum']);
    }

    public function test_object_codes_are_an_enum_with_titles(): void
    {
        $this->makeOwners(2);

        $docs = $this->generateDocs();
        $properties = $docs['paths']['/records']['post']['requestBody']['content']['application/json']['schema']['properties'];

        $this->assertSame(self::OWNER_SCHEMA, $this->refOf($properties['owner_code']));

        $component = $docs['components']['schemas'][self::OWNER_SCHEMA];

        $this->assertSame(['OWN-1', 'OWN-2'], $component['enum']);
        $this->assertSame(['Owner 1', 'Owner 2'], $component['x-enumNames']);

        $this->assertContains(['type' => 'string', 'description' => 'Code with no "Owner" object'], $properties['any_owner_code']['anyOf']);
    }

    public function test_too_many_objects_become_a_described_string(): void
    {
        $this->makeOwners(ObjectCodeTransformer::CODES_LIMIT + 1);

        $component = $this->generateDocs()['components']['schemas'][self::OWNER_SCHEMA];

        $this->assertArrayNotHasKey('enum', $component);
        $this->assertSame('Code of a "Owner" object', $component['description']);
    }

    public function test_no_objects_become_a_described_string(): void
    {
        $component = $this->generateDocs()['components']['schemas'][self::OWNER_SCHEMA];

        $this->assertArrayNotHasKey('enum', $component);
        $this->assertArrayNotHasKey('x-enumNames', $component);
        $this->assertSame('Code of a "Owner" object', $component['description']);
    }

    public function test_descriptions_follow_the_locale(): void
    {
        $this->app->setLocale('ru');

        $tag = $this->requestSchema()['properties']['tag'];

        $this->assertContains(['type' => 'string', 'description' => 'Код, которого нет в справочнике'], $tag['anyOf']);
    }
}
