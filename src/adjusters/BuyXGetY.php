<?php

namespace webdna\commerce\enhancedpromotions\adjusters;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\commerce\adjusters\Discount as DiscountAdjuster;
use webdna\commerce\enhancedpromotions\EnhancedPromotions;
use webdna\commerce\enhancedpromotions\records\CouponCode;
use craft\commerce\helpers\Currency;
use craft\commerce\models\Discount as DiscountModel;
use craft\commerce\models\Coupon;
use craft\commerce\models\LineItem;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\records\Discount as DiscountRecord;
use craft\helpers\ArrayHelper;

class BuyXGetY extends DiscountAdjuster
{
    /**
     * @var Order
     */
    private Order $_order;
    
    /**
     * @var float
     */
    private float $_discountTotal = 0;
    
    /**
     * Temporary feature flag for testing
     *
     * @var bool
     */
    private bool $_spreadBaseOrderDiscountsToLineItems = true;
    
    /**
     * @var array
     */
    private array $_discountUnitPricesByLineItem = [];
    
    
    

    public function adjust(Order $order): array
    {
        $this->_order = $order;
        
        $adjustments = [];
        $availableDiscounts = [];
        $discounts = Collect(EnhancedPromotions::getInstance()->discounts->getAllActiveDiscounts($order));
        $discounts = $discounts->filter(function($d) { return $d->getType() == (new \ReflectionClass($this))->getShortName(); });

        foreach ($discounts as $discount) {
            if (Commerce::getInstance()->getDiscounts()->matchOrder($order, $discount)) {
                $availableDiscounts[] = $discount;
            }
        }
        
        foreach ($this->_order->getLineItems() as $lineItem) {
            $lineItemHashId = spl_object_hash($lineItem);
            $lineItemDiscountAmount = $lineItem->getDiscount();
            if ($lineItemDiscountAmount) {
                $discountedUnitPrice = $lineItem->salePrice + Currency::round($lineItemDiscountAmount / $lineItem->qty);
                $this->_discountUnitPricesByLineItem[$lineItemHashId] = $discountedUnitPrice;
            }
        }
        
        foreach ($availableDiscounts as $discount) {
            $newAdjustments = $this->_getAdjustments($discount);
            if ($newAdjustments) {
                array_push($adjustments, ...$newAdjustments);
        
                if ($discount->stopProcessing) {
                    break;
                }
            }
        }
        
        return $adjustments;
    }
    
    
    private function _createOrderAdjustment(DiscountModel $discount): OrderAdjustment
    {
        //preparing model
        $adjustment = new OrderAdjustment();
        $adjustment->type = self::ADJUSTMENT_TYPE;
        $adjustment->name = $discount->name;
        $adjustment->setOrder($this->_order);
        $adjustment->description = $discount->description;
        $snapshot = $discount->toArray();
        $snapshot['discountUseId'] = $discount->id ?? null;
        $adjustment->sourceSnapshot = $snapshot;
    
        return $adjustment;
    }
    
    /**
     * @return OrderAdjustment[]|false
     */
    private function _getAdjustments(DiscountModel $discount): array|false
    {
        $adjustments = [];
    
        $matchingLineIds = [];
        $matchingLinePrices = collect([]);
        $totalQty = 0;
        
        foreach ($this->_order->getLineItems() as $item) {
            $lineItemHashId = spl_object_hash($item);
            // Order is already a match to this discount, or we wouldn't get here.
            if (Commerce::getInstance()->getDiscounts()->matchLineItem($item, $discount, false)) {
                $matchingLineIds[] = $lineItemHashId;
                $matchingLinePrices[] = [
                    'id' => $item->id, 
                    'price' => $item->salePrice,
                    'qty' => $item->qty,
                ];
                $totalQty += $item->qty;
            }
        }
        
        $xToDiscount = floor($totalQty / $discount->purchaseQty) * $discount->data['qty'];
        
    
        foreach (collect($this->_order->getLineItems())->sortBy('salePrice') as $item) {
            $lineItemHashId = spl_object_hash($item);
            if ($matchingLineIds && in_array($lineItemHashId, $matchingLineIds, false)) {
                $adjustment = $this->_createOrderAdjustment($discount);
                $adjustment->setLineItem($item);
                $adjustment->amount = 0;
                
                
                
                if ($matchingLinePrices->contains('id', $item->id)) {
                    $discountQty = $xToDiscount;
                    if ($item->qty < $xToDiscount) {
                        $discountQty = $item->qty;
                    }
                    $xToDiscount -= $discountQty;
                    $adjustment->amount = -($item->salePrice * $discountQty);
                }
                
    
                if ($adjustment->amount != 0) {
                    $adjustments[] = $adjustment;
                }
            }
        }
    
        // only display adjustment if an amount was calculated
        if (!count($adjustments)) {
            return false;
        }
    
    
        return $adjustments;
    }
    
}