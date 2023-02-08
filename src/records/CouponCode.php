<?php

namespace webdna\commerce\enhancedpromotions\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Coupon Code record
 */
class CouponCode extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commerce-enhanced-promotions_couponcodes}}';
    }
}
