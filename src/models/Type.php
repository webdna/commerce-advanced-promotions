<?php

namespace webdna\commerce\enhancedpromotions\models;

use Craft;
use craft\base\Model;

/**
 * Discount model
 */
class Type extends Model
{
    public string $name = '';
    
    public mixed $data = [];
    
    public function getClassname(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
