<?php

namespace webdna\commerce\enhancedpromotions\models;

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
