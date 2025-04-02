<?php

namespace examples;

use yii\db\ActiveRecord;
use LaithZraikat\UUID\UUIDBehavior;

/**
 * Example model demonstrating the UUID behavior
 *
 * @property string $id
 * @property string $uuid
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 */
class ExampleModel extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'example_model';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['uuid'], 'string', 'max' => 36],
            [['name'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => UUIDBehavior::class,
                'attribute' => 'uuid',
                'method' => UUIDBehavior::METHOD_MYSQL, // or UUIDBehavior::METHOD_PHP
                'keepDashes' => true, // standard format with dashes
                'enableOnUpdate' => false, // don't regenerate on update
            ],
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
}
