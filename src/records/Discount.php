<?php

namespace webdna\commerce\enhancedpromotions\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Discount record
 */
class Discount extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commerce-enhanced-promotions_discounts}}';
    }
}
