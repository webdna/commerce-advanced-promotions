<?php

namespace webdna\commerce\advancedpromotions\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Sale record
 */
class Sale extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commerce-advanced-promotions_sales}}';
    }
}
