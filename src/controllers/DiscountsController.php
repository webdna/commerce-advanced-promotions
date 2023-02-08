<?php

namespace webdna\commerce\enhancedpromotions\controllers;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\controllers\DiscountsController as CommerceDiscountsController;
use craft\web\Controller;
use yii\web\Response;

/**
 * Discounts controller
 */
class DiscountsController extends CommerceDiscountsController
{
    /**
     * commerce-enhanced-promotions/discounts action
     */
    public function actionIndex(): Response
    {
        $discounts = Commerce::getInstance()->getDiscounts()->getAllDiscounts();
        return $this->renderTemplate('commerce-enhanced-promotions/discounts/index', compact('discounts'));
    }
    
}
