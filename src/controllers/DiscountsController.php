<?php

namespace webdna\commerce\advancedpromotions\controllers;

use Craft;
use craft\commerce\base\Purchasable;
use craft\commerce\base\PurchasableInterface;
use craft\commerce\controllers\DiscountsController as CommerceDiscountsController;
use craft\commerce\elements\Product;
use craft\commerce\helpers\DebugPanel;
use craft\commerce\helpers\Localization;
use craft\commerce\models\Coupon;
use craft\commerce\models\Discount;
use craft\commerce\models\Sale;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Discount as DiscountRecord;
use craft\commerce\services\Coupons;
use craft\commerce\web\assets\coupons\CouponsAsset;
use craft\elements\Category;
use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\i18n\Locale;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;
use function explode;
use function get_class;

/**
 * Discounts controller
 */
class DiscountsController extends CommerceDiscountsController
{
    /**
     * commerce-advanced-promotions/discounts action
     */
    public function actionIndex(): Response
    {
        $discounts = Commerce::getInstance()->getDiscounts()->getAllDiscounts();
        return $this->renderTemplate('commerce-advanced-promotions/discounts/index', compact('discounts'));
    }
    
    /**
     * @param int|null $id
     * @param Discount|null $discount
     * @throws HttpException
     */
    public function actionEdit(int $id = null, Discount $discount = null): Response
    {
        if ($id === null) {
            $this->requirePermission('commerce-createDiscounts');
        } else {
            $this->requirePermission('commerce-editDiscounts');
        }
    
        $variables = compact('id', 'discount');
        $variables['isNewDiscount'] = false;
    
        if (!$variables['discount']) {
            if ($variables['id']) {
                $variables['discount'] = Commerce::getInstance()->getDiscounts()->getDiscountById($variables['id']);
    
                if (!$variables['discount']) {
                    throw new HttpException(404);
                }
            } else {
                $variables['discount'] = new Discount();
                $variables['discount']->allCategories = true;
                $variables['discount']->allPurchasables = true;
                $variables['isNewDiscount'] = true;
            }
        }
    
        DebugPanel::prependOrAppendModelTab(model: $variables['discount'], prepend: true);
    
        $this->_populateVariables($variables);
        $variables['percentSymbol'] = Craft::$app->getFormattingLocale()->getNumberSymbol(Locale::SYMBOL_PERCENT);
        $this->getView()->registerAssetBundle(CouponsAsset::class);
    
        return $this->renderTemplate('commerce-advanced-promotions/discounts/_edit', $variables);
    }
    
    
    
    private function _populateVariables(array &$variables): void
    {
        if ($variables['discount']->id) {
            $variables['title'] = $variables['discount']->name;
        } else {
            $variables['title'] = Craft::t('commerce', 'Create a Discount');
        }
    
        // getting user groups map
        if (Craft::$app->getEdition() == Craft::Pro) {
            $groups = Craft::$app->getUserGroups()->getAllGroups();
            $variables['groups'] = ArrayHelper::map($groups, 'id', 'name');
        } else {
            $variables['groups'] = [];
        }
    
        $flipNegativeNumberAttributes = ['baseDiscount', 'perItemDiscount'];
        foreach ($flipNegativeNumberAttributes as $attr) {
            if (!isset($variables['discount']->{$attr})) {
                continue;
            }
    
            if ($variables['discount']->{$attr} != 0) {
                $variables['discount']->{$attr} *= -1;
            } else {
                $variables['discount']->{$attr} = 0;
            }
        }
    
        $variables['counterTypeTotal'] = self::DISCOUNT_COUNTER_TYPE_TOTAL;
        $variables['counterTypeEmail'] = self::DISCOUNT_COUNTER_TYPE_EMAIL;
        $variables['counterTypeUser'] = self::DISCOUNT_COUNTER_TYPE_CUSTOMER;
    
        if ($variables['discount']->id) {
            $variables['emailUsage'] = Commerce::getInstance()->getDiscounts()->getEmailUsageStatsById($variables['discount']->id);
            $variables['customerUsage'] = Commerce::getInstance()->getDiscounts()->getCustomerUsageStatsById($variables['discount']->id);
        } else {
            $variables['emailUsage'] = 0;
            $variables['customerUsage'] = 0;
        }
    
        $currency = Commerce::getInstance()->getPaymentCurrencies()->getPrimaryPaymentCurrency();
        $currencyName = $currency ? $currency->getCurrency() : '';
        $percentSymbol = Craft::$app->getFormattingLocale()->getNumberSymbol(Locale::SYMBOL_PERCENT);
    
        $variables['baseDiscountTypes'] = [
            DiscountRecord::BASE_DISCOUNT_TYPE_VALUE => Craft::t('commerce', $currencyName . ' value'),
        ];
    
        if ($variables['discount']->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL) {
            $variables['baseDiscountTypes'][DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL] = Craft::t('commerce', '{pct} off total original price and shipping total (deprecated)', [
                'pct' => $percentSymbol,
            ]);
        }
    
        if ($variables['discount']->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL_DISCOUNTED) {
            $variables['baseDiscountTypes'][DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL_DISCOUNTED] = Craft::t('commerce', '{pct} off total discounted price and shipping total (deprecated)', [
                'pct' => $percentSymbol,
            ]);
        }
    
        if ($variables['discount']->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_ITEMS) {
            $variables['baseDiscountTypes'][DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_ITEMS] = Craft::t('commerce', '{pct} off total original price (deprecated)', [
                'pct' => $percentSymbol,
            ]);
        }
    
        if ($variables['discount']->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_ITEMS_DISCOUNTED) {
            $variables['baseDiscountTypes'][DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_ITEMS_DISCOUNTED] = Craft::t('commerce', '{pct} off total discounted price (deprecated)', [
                'pct' => $percentSymbol,
            ]);
        }
    
        $variables['categoryElementType'] = Category::class;
        $variables['categories'] = null;
        $categories = [];
    
        if (empty($variables['id']) && $this->request->getParam('categoryIds')) {
            $categoryIds = explode('|', $this->request->getParam('categoryIds'));
        } else {
            $categoryIds = $variables['discount']->getCategoryIds();
        }
    
        foreach ($categoryIds as $categoryId) {
            $id = (int)$categoryId;
            $categories[] = Craft::$app->getElements()->getElementById($id);
        }
    
        $variables['categories'] = $categories;
    
        $variables['categoryRelationshipTypeOptions'] = [
            DiscountRecord::CATEGORY_RELATIONSHIP_TYPE_SOURCE => Craft::t('commerce', 'Source - The category relationship field is on the purchasable'),
            DiscountRecord::CATEGORY_RELATIONSHIP_TYPE_TARGET => Craft::t('commerce', 'Target - The purchasable relationship field is on the category'),
            DiscountRecord::CATEGORY_RELATIONSHIP_TYPE_BOTH => Craft::t('commerce', 'Either (Default) - The relationship field is on the purchasable or the category'),
        ];
    
        $variables['appliedTo'] = [
            DiscountRecord::APPLIED_TO_MATCHING_LINE_ITEMS => Craft::t('commerce', 'Discount the matching items only'),
            DiscountRecord::APPLIED_TO_ALL_LINE_ITEMS => Craft::t('commerce', 'Discount all line items'),
        ];
    
        $variables['purchasables'] = null;
    
        if (empty($variables['id']) && $this->request->getParam('purchasableIds')) {
            $purchasableIdsFromUrl = explode('|', $this->request->getParam('purchasableIds'));
            $purchasableIds = [];
            foreach ($purchasableIdsFromUrl as $purchasableId) {
                $purchasable = Craft::$app->getElements()->getElementById((int)$purchasableId);
                if ($purchasable instanceof Product) {
                    $purchasableIds[] = $purchasable->defaultVariantId; // this would only be null if we are duplicating a variant, otherwise should never be null
                } else {
                    $purchasableIds[] = $purchasableId;
                }
            }
        } else {
            $purchasableIds = $variables['discount']->getPurchasableIds();
        }
    
        $purchasableIds = array_filter($purchasableIds);
    
        $purchasables = [];
        foreach ($purchasableIds as $purchasableId) {
            $purchasable = Craft::$app->getElements()->getElementById((int)$purchasableId);
            if ($purchasable instanceof PurchasableInterface) {
                $class = get_class($purchasable);
                $purchasables[$class] = $purchasables[$class] ?? [];
                $purchasables[$class][] = $purchasable;
            }
        }
        $variables['purchasables'] = $purchasables;
    
        $variables['purchasableTypes'] = [];
        $purchasableTypes = Commerce::getInstance()->getPurchasables()->getAllPurchasableElementTypes();
    
        /** @var Purchasable $purchasableType */
        foreach ($purchasableTypes as $purchasableType) {
            $variables['purchasableTypes'][] = [
                'name' => $purchasableType::displayName(),
                'elementType' => $purchasableType,
            ];
        }
    }
}
