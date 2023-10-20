<?php

namespace webdna\commerce\enhancedpromotions\controllers;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\controllers\DiscountsController as CommerceDiscountsController;
use craft\commerce\base\Purchasable;
use craft\commerce\base\PurchasableInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\commerce\helpers\DebugPanel;
use craft\commerce\helpers\Localization;
use craft\web\Controller;
use craft\commerce\records\Discount as DiscountRecord;
use craft\commerce\services\Coupons;
use craft\commerce\web\assets\coupons\CouponsAsset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\i18n\Locale;
use webdna\commerce\enhancedpromotions\models\Discount;
use webdna\commerce\enhancedpromotions\models\types\BuyXGetY;
use webdna\commerce\enhancedpromotions\EnhancedPromotions;
use webdna\commerce\enhancedpromotions\behaviors\DiscountBehavior;
use yii\web\Response;

/**
 * Discounts controller
 */
class DiscountsController extends CommerceDiscountsController
{
    /**
     * commerce-enhanced-promotions/discounts action
     */
    public function actionIndex(string $type=null): Response
    {
        $discounts = Collect(Commerce::getInstance()->getDiscounts()->getAllDiscounts());
        
        $discounts = $discounts->filter(function($d) use ($type) { return $d->getType() == $type; });
        
        return $this->renderTemplate('commerce-enhanced-promotions/discounts/index', compact('discounts', 'type'));
    }
    
    public function actionEditType(int $id = null, Discount $discount = null, string $type=null): Response
    {
        if (!$discount) {
            $typeClass = EnhancedPromotions::getInstance()->discounts->getDiscountTypeByClassname($type);
            $discount = new $typeClass();
        }
        
        $discount->setType($typeClass->classname);
        
        return parent::actionEdit($id, $discount);
        
    }
    
}
