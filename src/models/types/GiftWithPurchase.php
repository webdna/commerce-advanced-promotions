<?php

namespace webdna\commerce\enhancedpromotions\models\types;

use Craft;
use craft\base\Model;
use webdna\commerce\enhancedpromotions\models\Discount;
use craft\commerce\services\Coupons;
use craft\commerce\records\Discount as DiscountRecord;

/**
 * Discount model
 */
class GiftWithPurchase extends Discount
{
    public string $label = 'Gift With Purchase';
    
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
            
        //$rules[] = [['purchaseQty'], 'compare', 'compareValue' => 1, 'operator' => '>=', 'type' => 'number'];
        
        return $rules;
    }
}
