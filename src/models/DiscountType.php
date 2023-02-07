<?php

namespace webdna\commerce\advancedpromotions\models;

use Craft;
use craft\base\Model;

/**
 * Discount Type model
 */
class DiscountType extends Model
{
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
}
