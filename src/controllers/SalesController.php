<?php

namespace webdna\commerce\advancedpromotions\controllers;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\controllers\SalesController as CommerceSalesController;
use craft\web\Controller;
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
        return $this->renderTemplate('commerce-advanced-promotions/sales/index', compact('sales'));
    }
}
