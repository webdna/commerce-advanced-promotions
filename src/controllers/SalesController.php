<?php

namespace webdna\commerce\enhancedpromotions\controllers;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\controllers\SalesController as CommerceSalesController;
use craft\web\Controller;
use webdna\commerce\enhancedpromotions\EnhancedPromotions;
use yii\web\Response;

/**
 * Sales controller
 */
class SalesController extends CommerceSalesController
{
    /**
     * commerce-advanced-promotions/sales action
     */
    public function actionIndex(): Response
    {
        $sales = Commerce::getInstance()->getSales()->getAllSales();
        return $this->renderTemplate('commerce-enhanced-promotions/sales/index', compact('sales'));
    }
}
