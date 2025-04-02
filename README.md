# Yii2 UUID Behavior

A Yii2 behavior for automatically generating and formatting UUIDs for ActiveRecord models.

## Features

- Automatically generates UUIDs for new records
- Supports both PHP-generated and MySQL-generated UUIDs
- Configurable UUID format (with or without dashes)
- Handles existing UUIDs and ensures proper formatting
- Optional UUID generation on record updates

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
composer require laith-zraikat/yii2-uuid-behavior
```

## Usage

Attach the behavior to your ActiveRecord model:

```php
use LaithZraikat\UUID\UUIDBehavior;

class MyModel extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => UUIDBehavior::class,
                'attribute' => 'uuid', // the attribute that will store the UUID
                'method' => UUIDBehavior::METHOD_MYSQL, // or UUIDBehavior::METHOD_PHP
                'keepDashes' => true, // whether to keep dashes in the UUID
                'enableOnUpdate' => false, // whether to generate UUID on update
            ],
        ];
    }
}
```

## Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `attribute` | string | `'uuid'` | The model attribute that will store the UUID |
| `method` | string | `UUIDBehavior::METHOD_MYSQL` | UUID generation method (`METHOD_MYSQL` or `METHOD_PHP`) |
| `keepDashes` | boolean | `true` | Whether to keep dashes in the UUID |
| `enableOnUpdate` | boolean | `false` | Whether to generate UUID on model update |

## UUID Generation Methods

### MySQL Method (`METHOD_MYSQL`)

Uses MySQL's `UUID()` function to generate UUIDs. This method:
- Has better performance and uniqueness guarantees
- Requires a database call to generate the UUID
- The UUID is only available after the record is saved

### PHP Method (`METHOD_PHP`)

Generates UUIDs using PHP's `mt_rand()` function. This method:
- Makes the UUID available before saving the record
- Works with any database backend
- Slightly less performant than the MySQL method

## UUID Formatting

The behavior supports two UUID formats:

1. **Standard format** (with dashes): `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
2. **Compact format** (without dashes): `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

Use the `keepDashes` option to control the format.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
