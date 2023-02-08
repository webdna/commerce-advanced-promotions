<?php

namespace webdna\commerce\enhancedpromotions\models;

use Craft;
use craft\base\Model;

/**
 * Discount model
 */
class Discount extends Model
{
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
}
