<?php

namespace webdna\commerce\advancedpromotions\models;

use Craft;
use craft\base\Model;

/**
 * Sale model
 */
class Sale extends Model
{
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
}
