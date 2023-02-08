<?php

namespace webdna\commerce\enhancedpromotions\adjusters;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\commerce\adjusters\Discount as DiscountAdjuster;
use webdna\commerce\enhancedpromotions\records\CouponCode;

class Discount extends DiscountAdjuster
{
	public function adjust(Order $order): array
	{
		$adjustments = [];
		
		foreach ($order->couponCodes as $code) {
			$order->couponCode = $code;
			array_push($adjustments, ...parent::adjust($order));
				
			$discount = Commerce::getInstance()->getDiscounts()->getDiscountByCode($code);
			if ($discount->stopProcessing) {
				break;
			}
		}
		
		$order->couponCode = null;
		
		return $adjustments;
	}
}