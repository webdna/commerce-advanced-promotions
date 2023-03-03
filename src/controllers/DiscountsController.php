<?php

namespace webdna\commerce\enhancedpromotions\controllers;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\commerce\controllers\DiscountsController as CommerceDiscountsController;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\web\Controller;
use webdna\commerce\enhancedpromotions\models\Discount;
use webdna\commerce\enhancedpromotions\EnhancedPromotions;
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
        if ($type) {
            $discounts = EnhancedPromotions::getInstance()->discounts->getAllDiscountsByType($type);
            return $this->renderTemplate("commerce-enhanced-promotions/types/index", compact('discounts', 'type'));
        }
        
        $discounts = Commerce::getInstance()->getDiscounts()->getAllDiscounts();
        return $this->renderTemplate('commerce-enhanced-promotions/discounts/index', compact('discounts'));
    }
    
    public function actionEditType(int $id = null, Discount $discount = null, string $type=null): Response
    {
        if ($id === null) {
            $this->requirePermission('commerce-createDiscounts');
        } else {
            $this->requirePermission('commerce-editDiscounts');
        }
        
        $variables = compact('id', 'discount');
        $variables['type'] = EnhancedPromotions::getInstance()->discounts->getDiscountTypeByClassname($type);
        $variables['isNewDiscount'] = false;
        
        if (!$variables['discount']) {
            if ($variables['id']) {
                $variables['discount'] = EnhancedPromotions::getInstance()->discounts->getDiscountTypeById($variables['id']);
        
                if (!$variables['discount']) {
                    throw new HttpException(404);
                }
            } else {
                $variables['discount'] = new Discount();
                $variables['discount']->type = $variables['type']->classname;
                $variables['discount']->data = $variables['type']->data;
                $variables['isNewDiscount'] = true;
            }
        }
        
        //DebugPanel::prependOrAppendModelTab(model: $variables['discount'], prepend: true);
        
        $this->_populateVariables($variables);
        //$this->getView()->registerAssetBundle(CouponsAsset::class);
        
        return $this->renderTemplate('commerce-enhanced-promotions/_types/'.$type, $variables);
    }
    
    /*public function actionSave(): void
    {
        
    }*/
    
    /*public function actionReorder(): Response
    {
        
    }*/
    
    /*public function actionDelete(): Response
    {
        
    }*/
    
    /*public function actionUpdateStatus(): void
    {
        
    }*/
    
    /*public function actionGetDiscountsByPurchasableId(): Response
    {
        
    }*/
    
    private function _populateVariables(array &$variables): void
    {
        if ($variables['discount']->id) {
            $variables['title'] = $variables['discount']->name;
        } else {
            $variables['title'] = Craft::t('commerce', 'Create a '.$variables['type']->name.' Discount');
        }
    
        // getting user groups map
        if (Craft::$app->getEdition() == Craft::Pro) {
            $groups = Craft::$app->getUserGroups()->getAllGroups();
            $variables['groups'] = ArrayHelper::map($groups, 'id', 'name');
        } else {
            $variables['groups'] = [];
        }
    }
}
