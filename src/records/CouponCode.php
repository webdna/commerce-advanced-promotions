<?php

namespace webdna\commerce\advancedpromotions\records;

use Craft;
use craft\db\ActiveRecord;

/**
 * Coupon Code record
 */
class CouponCode extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%commerce-advanced-promotions_couponcodes}}';
    }
}
