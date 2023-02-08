<?php

namespace webdna\commerce\enhancedpromotions\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Sale record
 */
class Sale extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commerce-enhanced-promotions_sales}}';
    }
}
