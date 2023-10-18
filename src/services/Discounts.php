<?php

namespace webdna\commerce\enhancedpromotions\services;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\ArrayHelper;
use craft\base\Model;
use yii\base\Component;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\commerce\models\Coupon;

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
            $types[$info['filename']] = $class->name;
        }
        return $types;
    }
    
    public function getDiscountTypeByClassname(string $classname): ?Model
    {
        $classname = '\\webdna\\commerce\\enhancedpromotions\\models\\types\\' . $classname;
        $class = new $classname();
        return $class;
    }
    
    public function getDiscountTypeById(int $id): ?Discount
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
    
    public function saveDiscountType(Discount $model, bool $runValidation = true): bool
    {
        
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
