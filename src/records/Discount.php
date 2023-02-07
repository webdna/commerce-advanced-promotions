<?php

namespace webdna\commerce\advancedpromotions\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Discount record
 */
class Discount extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commerce-advanced-promotions_discounts}}';
    }
}
