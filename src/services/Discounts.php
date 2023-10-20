<?php

namespace webdna\commerce\enhancedpromotions\services;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\base\Model;
use Throwable;
use yii\base\Component;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\commerce\models\Coupon;
use craft\commerce\models\Discount;
use webdna\commerce\enhancedpromotions\records\Discount as DiscountRecord;

/**
 * Discounts service
 */
class Discounts extends Component
{
    public function getDiscountTypes(): array
    {
        $types = [];
        
        $files = FileHelper::findFiles(Craft::$app->getPath()->getVendorPath().'/webdna/commerce-enhanced-promotions/src/models/types');
        
        foreach ($files as $key => $type) {
            $info = pathinfo($type);
            $class = $this->getDiscountTypeByClassname($info['filename']);
            $types[$info['filename']] = $class->label;
        }
        return $types;
    }
    
    public function getDiscountTypeByClassname(string $classname): ?Model
    {
        $classname = '\\webdna\\commerce\\enhancedpromotions\\models\\types\\' . $classname;
        $class = new $classname();
        return $class;
    }
    
    public function getDiscountById(int $id): ?Discount
    {
        
    }
    
    public function getAllDiscountsByType(string $type): array
    {
        return [];
    }
    
    public function getAllActiveDiscountsByType(string $type, Order $order = null): array
    {
        
    }
    
    public function getDiscountsRelatedToPurchasable(PurchasableInterface $purchasable): array
    {
        
    }
    
    public function matchLineItem(LineItem $lineItem, Discount $discount, bool $matchOrder = false): bool
    {
        
    }
    
    public function matchOrder(Order $order, Discount $discount): bool
    {
        
    }
    
    public function saveDiscount(Discount $model): bool
    {
        if ($record = DiscountRecord::find()->where(['discountId'=>$model->id])->one()) {
        } else {
            $record = new DiscountRecord();
        }
        
        try {
            $record->discountId = $model->id;
            $record->type = $model->getType();
            $record->data = $model->getData();
            
            $record->save();
            
            return true;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function deleteDiscountTypeById(int $id): bool
    {
        
    }
    
    public function reorderDiscountTypes(array $ids): bool
    {
        
    }
    
    
    public function isValidCode(string $code): mixed
    {
        $availableDiscounts = [];
        $discounts = Commerce::getInstance()->getDiscounts()->getAllActiveDiscounts();
        
        foreach ($discounts as $discount) {
            $coupons = $discount->getCoupons();
            if (!empty($coupons)) {
                if (ArrayHelper::firstWhere($coupons, static fn(Coupon $coupon) => (strcasecmp($coupon->code, $code) == 0) && ($coupon->maxUses === null || $coupon->maxUses > $coupon->uses))) {
                    return $discount;
                }
            }
        }
        
        return false;
    }

}
