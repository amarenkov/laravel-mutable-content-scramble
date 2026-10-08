<?php

namespace Amarenkov\MutableContentScramble\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;

use Amarenkov\MutableContent\Models\ModelWithFields;

use Amarenkov\MutableContent\Domain\Field\TypeSettings;
use Amarenkov\MutableContent\Domain\Field\Lov\Type;

use Amarenkov\MutableContent\Attributes\Class\Label as ClassLabel;
use Amarenkov\MutableContent\Attributes\Field\Common\Code as FieldCode;

use Amarenkov\MutableContent\Attributes\FieldAttr\Common\Type as CFAType;
use Amarenkov\MutableContent\Attributes\FieldAttr\Common\Label as CFALabel;
use Amarenkov\MutableContent\Attributes\FieldAttr\Common\IsRequired as CFAIsRequired;
use Amarenkov\MutableContent\Attributes\FieldAttr\Common\LovCode as CFALovCode;
use Amarenkov\MutableContent\Attributes\FieldAttr\Common\ObjectClass as CFAObjectClass;
use Amarenkov\MutableContent\Attributes\FieldAttr\Common\TypeSettings as CFATypeSettings;

use Amarenkov\MutableContentScramble\Tests\Fixtures\Lovs\RecordStatus;

#[Table('records')]
#[Fillable(['id', 'fields'])]
#[ClassLabel('Record')]
#[FieldCode]
class Record extends ModelWithFields
{
    // const
    #[CFAType(Type::TYPE_INT), CFALabel('Quantity'), CFAIsRequired]
    public const FIELD_QUANTITY = 'quantity';

    #[CFAType(Type::TYPE_LOV_ITEM), CFALabel('Status'), CFALovCode(RecordStatus::CODE)]
    public const FIELD_STATUS = 'status';

    #[CFAType(Type::TYPE_LOV_ITEM), CFALabel('Tag'), CFALovCode(RecordStatus::CODE), CFATypeSettings([TypeSettings::ALLOW_UNLISTED_CODES => true])]
    public const FIELD_TAG = 'tag';

    #[CFAType(Type::TYPE_LOV), CFALabel('Source LOV')]
    public const FIELD_SOURCE_LOV = 'source_lov';

    #[CFAType(Type::TYPE_OBJECT), CFALabel('Owner code'), CFAObjectClass(Owner::class), CFATypeSettings([TypeSettings::LINK_BY_CODE => true])]
    public const FIELD_OWNER_CODE = 'owner_code';

    #[CFAType(Type::TYPE_OBJECT), CFALabel('Any owner code'), CFAObjectClass(Owner::class), CFATypeSettings([TypeSettings::LINK_BY_CODE => true, TypeSettings::ALLOW_UNLISTED_CODES => true])]
    public const FIELD_ANY_OWNER_CODE = 'any_owner_code';

    // static
    protected static array|bool|null $fieldDefinitions = null;
}
