# amarenkov/laravel-mutable-content-scramble

[![tests](https://github.com/amarenkov/laravel-mutable-content-scramble/actions/workflows/tests.yml/badge.svg)](https://github.com/amarenkov/laravel-mutable-content-scramble/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/amarenkov/laravel-mutable-content-scramble)](https://packagist.org/packages/amarenkov/laravel-mutable-content-scramble)

OpenAPI docs for
[`amarenkov/laravel-mutable-content`](https://github.com/amarenkov/laravel-mutable-content)
via [Scramble](https://scramble.dedoc.co/).

Request parameters get human-readable field labels in the docs, and values from lists of values
get enums with a code-to-label table instead of bare codes.

## Features

- **Field labels in parameter descriptions.** The `#[MutableRequest]` attribute on a controller
  method links request parameters to a model class, and field labels are put into the docs.
- **Lists of values as enums.** The `InLov` and `InLovs` validation rules become schemas with an
  enum of allowed values and a "code → label" table.
- **References by code.** For an `object` field linked by code (`link_by_code` in the core), the
  schema lists object codes with their titles, like a list of values; with unlisted codes
  allowed, any string is accepted too. With more than 200 objects, the schema is a plain string
  described by the class label.
- **Local `elements-local` renderer.** Stoplight Elements is bundled with the package, so the docs
  do not load anything from a CDN. The view also adds `X-XSRF-TOKEN` to Try It requests so that
  Sanctum cookie authentication works.

## Requirements

- PHP 8.4
- `amarenkov/laravel-mutable-content`
- `dedoc/scramble` ^0.13

## Installation

```bash
composer require amarenkov/laravel-mutable-content-scramble

php artisan vendor:publish --tag=mutable-content-scramble-public
```

To enable the local renderer, in `config/scramble.php`:

```php
'renderer' => 'elements-local',

'renderers' => [
    'elements-local' => [
        'view' => 'mutable-content-scramble::elements-local',
        'theme' => 'light',
        'hideTryIt' => false,
        'hideSchemas' => false,
        'logo' => '',
        'tryItCredentialsPolicy' => 'include',
        'layout' => 'responsive',
        'router' => 'hash',
    ],
    // ...
],
```

The parameter extractor, rule transformers and schema extensions are registered automatically.

## Usage

```php
use Amarenkov\MutableContent\Helpers\RuleHelper;
use Amarenkov\MutableContentScramble\Attributes\MutableRequest;

#[MutableRequest(Project::class)]
#[MutableRequest(Task::class, 'tasks.*')]
public function store(Request $request)
{
    $data = $request->validate(
        RuleHelper::getValidationRules(Project::class) +
        ['tasks' => 'array|required'] +
        RuleHelper::getValidationRules(Task::class, 'tasks.*')
    );

    // ...
}
```

The attribute only affects the docs; it does not validate anything. The first argument is the
model class, the second (optional) one is the request parameter prefix.

## Translations

Schema descriptions are in `lang/{en,ru}/schema.php` under the `mutable-content-scramble`
namespace. Publish them to override:

```bash
php artisan vendor:publish --tag=mutable-content-scramble-lang
```

## Caveats

The attribute is easy to forget when a new class is added to a request. Nothing breaks, some
parameters just have no description in the docs.

Scramble has no stable API yet: `RuleTransformer`, `ParameterExtractor` and
`TypeToSchemaExtension` change between minor versions, so check the classes in `src/Scramble/`
after updating Scramble.

## License

MIT. See [LICENSE](LICENSE). Bundled Stoplight Elements is licensed under Apache 2.0, and parts
adapted from Scramble under MIT; see [NOTICE](NOTICE).
