<?php

namespace LaithZraikat\UUID;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\base\InvalidConfigException;

/**
 * UUID Behavior
 * 
 * This behavior automatically generates a UUID for a model attribute and
 * ensures it has the proper format with dashes.
 * 
 * It supports both PHP-generated UUIDs and MySQL-generated UUIDs.
 * It also supports UUIDs with or without dashes.
 */
class UUIDBehavior extends Behavior
{
    /**
     * UUID generation method constants
     */
    const METHOD_PHP = 'php';
    const METHOD_MYSQL = 'mysql';
    
    /**
     * @var string the attribute that will receive the UUID value
     */
    public $attribute = 'uuid';
    
    /**
     * @var bool whether to apply the behavior on update
     */
    public $enableOnUpdate = false;
    
    /**
     * @var string the UUID generation method to use
     * Possible values:
     * - UUIDBehavior::METHOD_PHP: Generate UUID in PHP (allows access to UUID before save)
     * - UUIDBehavior::METHOD_MYSQL: Use MySQL's UUID() function (better performance and uniqueness)
     */
    public $method = self::METHOD_MYSQL;
    
    /**
     * @var bool whether to keep dashes in the UUID
     * - true: Keep dashes in the UUID (standard format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
     * - false: Remove dashes from the UUID (compact format: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx)
     */
    public $keepDashes = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // Validate the method
        if (!in_array($this->method, [self::METHOD_PHP, self::METHOD_MYSQL])) {
            throw new InvalidConfigException(
                "Invalid UUID generation method: {$this->method}. " .
                "Allowed methods are: " . self::METHOD_PHP . " and " . self::METHOD_MYSQL
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'ensureUuid',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'ensureUuid',
        ];
    }

    /**
     * Ensures the model has a properly formatted UUID
     */
    public function ensureUuid($event)
    {
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        
        // Skip if this is an update and we don't want to enable on update
        if ($owner->isNewRecord === false && $this->enableOnUpdate === false) {
            return;
        }
        
        $attribute = $this->attribute;
        
        // If UUID exists but format doesn't match desired format (with/without dashes)
        if (!empty($owner->$attribute)) {
            $hasDashes = strpos($owner->$attribute, '-') !== false;
            
            // If we want dashes but UUID doesn't have them
            if ($this->keepDashes && !$hasDashes && strlen($owner->$attribute) == 32) {
                $owner->$attribute = $this->formatUuid($owner->$attribute);
            } 
            // If we don't want dashes but UUID has them
            elseif (!$this->keepDashes && $hasDashes) {
                $owner->$attribute = str_replace('-', '', $owner->$attribute);
            }
        } 
        // If UUID doesn't exist, generate a new one
        elseif (empty($owner->$attribute)) {
            switch ($this->method) {
                case self::METHOD_MYSQL:
                    if ($this->keepDashes) {
                        $owner->$attribute = new Expression('UUID()');
                    } else {
                        $owner->$attribute = new Expression("REPLACE(UUID(), '-', '')");
                    }
                    break;
                    
                case self::METHOD_PHP:
                    $uuid = $this->generateUuid();
                    if (!$this->keepDashes) {
                        $uuid = str_replace('-', '', $uuid);
                    }
                    $owner->$attribute = $uuid;
                    break;
                    
                default:
                    // This should never happen due to validation in init()
                    throw new InvalidConfigException(
                        "Invalid UUID generation method: {$this->method}. " .
                        "Allowed methods are: " . self::METHOD_PHP . " and " . self::METHOD_MYSQL
                    );
            }
        }
    }
    
    /**
     * Formats a UUID string by adding dashes in the standard format
     * 
     * @param string $uuid UUID string without dashes
     * @return string Formatted UUID with dashes
     */
    protected function formatUuid($uuid)
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($uuid, 0, 8),
            substr($uuid, 8, 4),
            substr($uuid, 12, 4),
            substr($uuid, 16, 4),
            substr($uuid, 20, 12)
        );
    }
    
    /**
     * Generates a new UUID v4 string using PHP
     * 
     * @return string UUID v4 with dashes
     */
    protected function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
