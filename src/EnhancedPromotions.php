<?php

namespace webdna\commerce\enhancedpromotions;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\base\Element;
use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Order;
use craft\commerce\events\DiscountEvent;
use craft\commerce\models\OrderNotice;
use craft\commerce\events\OrderNoticeEvent;
use craft\commerce\events\MatchOrderEvent;
use craft\commerce\events\MatchLineItemEvent;
use craft\commerce\events\DiscountAdjustmentsEvent;
use craft\commerce\services\Discounts as CommerceDiscounts;
use craft\commerce\models\Discount as CommerceDiscount;
use craft\commerce\adjusters\Discount as DiscountAdjuster;
use craft\commerce\models\LineItem;
use craft\commerce\services\OrderAdjustments;
use craft\events\DefineBehaviorsEvent;
use craft\events\DefineRulesEvent;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ElementHelper;
use craft\helpers\Db;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\Response;
use webdna\commerce\enhancedpromotions\adjusters\MultiCouponCodes as MultiCouponCodesAdjuster;
use webdna\commerce\enhancedpromotions\adjusters\BuyXGetY as BuyXGetYAdjuster;
use webdna\commerce\enhancedpromotions\behaviors\OrderBehavior;
use webdna\commerce\enhancedpromotions\behaviors\DiscountBehavior;
use webdna\commerce\enhancedpromotions\fields\Discounts as DiscountsField;
use webdna\commerce\enhancedpromotions\models\Settings;
use webdna\commerce\enhancedpromotions\services\DiscountTypes;
use webdna\commerce\enhancedpromotions\services\Discounts;
use webdna\commerce\enhancedpromotions\services\Sales;
use yii\base\Event;

/**
 * Enhanced Promotions plugin
 *
 * @method static EnhancedPromotions getInstance()
 * @method Settings getSettings()
 * @author webdna <info@webdna.co.uk>
 * @copyright webdna
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read Discounts $discounts
 * @property-read Sales $sales
 * @property-read DiscountTypes $discountTypes
 */
class EnhancedPromotions extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = false;

    public static function config(): array
    {
        return [
            'components' => [
                'discounts' => Discounts::class, 
                'sales' => Sales::class, 
                'discountTypes' => DiscountTypes::class
            ],
        ];
    }

    public function init()
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('commerce-enhanced-promotions/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        Event::on(
            Fields::class, 
            Fields::EVENT_REGISTER_FIELD_TYPES, 
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = DiscountsField::class;
            }
        );
        
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                
                $event->rules['commerce/promotions/sales'] = 'commerce-enhanced-promotions/sales/index';
                $event->rules['commerce/promotions/sales/new'] = 'commerce-enhanced-promotions/sales/edit';
                $event->rules['commerce/promotions/sales/<id:\d+>'] = 'commerce-enhanced-promotions/sales/edit';
                
                $event->rules['commerce/promotions/discounts'] = 'commerce-enhanced-promotions/discounts/index';
                $event->rules['commerce/promotions/discounts/new'] = 'commerce-enhanced-promotions/discounts/edit';
                $event->rules['commerce/promotions/discounts/<id:\d+>'] = 'commerce-enhanced-promotions/discounts/edit';
                
                $event->rules['commerce/promotions/types/<type:\w+>'] = 'commerce-enhanced-promotions/discounts/index';
                $event->rules['commerce/promotions/types/<type:\w+>/new'] = 'commerce-enhanced-promotions/discounts/edit-type';
                $event->rules['commerce/promotions/types/<type:\w+>/<id:\d+>'] = 'commerce-enhanced-promotions/discounts/edit-type';
            }
        );
        
        Event::on(
            Order::class,
            Order::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors['commerce:order'] = OrderBehavior::class;
            }
        );
        
        Event::on(
            CommerceDiscount::class,
            CommerceDiscount::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors['commerce:discount'] = DiscountBehavior::class;
            }
        );
        
        Event::on(
            CommerceDiscount::class,
            CommerceDiscount::EVENT_DEFINE_RULES,
            function(DefineRulesEvent $event) {
                
                if($type = $event->sender->getType()) {
                    $type = "\\webdna\\commerce\\enhancedpromotions\\models\\types\\$type";
                    $event->rules = (new $type())->rules();
                }
            }
        );
        
        Event::on(
            OrderAdjustments::class, 
            OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, 
            function(RegisterComponentTypesEvent $e) {
                if ($this->getSettings()->multiCouponCodes) {
                    $e->types[] = MultiCouponCodesAdjuster::class;
                    $e->types[] = BuyXGetYAdjuster::class;
                }
            }
        );
        
        Event::on(
            CommerceDiscounts::class,
            CommerceDiscounts::EVENT_AFTER_SAVE_DISCOUNT,
            function(DiscountEvent $event) {
                // @var Discount $discount
                $discount = $event->discount;
                // @var bool $isNew
                $isNew = $event->isNew;
                
                $type = Craft::$app->getRequest()->getBodyParam('type');
                $data = Craft::$app->getRequest()->getBodyParam('data');
                
                $discount->setType($type);
                $discount->setData($data);
    
                $this->discounts->saveDiscount($discount);
            }
        );
        
        Event::on(
            DiscountAdjuster::class,
            DiscountAdjuster::EVENT_AFTER_DISCOUNT_ADJUSTMENTS_CREATED,
            function(DiscountAdjustmentsEvent $event) {
                // @var Discount $discount
                $discount = $event->discount;
                
                if ($discount->getType()) {
                    $event->isValid = false;
                }
            }
        );
        
        /*Event::on(
            CommerceDiscounts::class,
            CommerceDiscounts::EVENT_DISCOUNT_MATCHES_LINE_ITEM,
            function(MatchLineItemEvent $event) {
                // @var Discount $discount
                $discount = $event->discount;
                
                if ($discount->getType()) {
                    $event->isValid = false;
                }
            }
        );
        Event::on(
            CommerceDiscounts::class,
            CommerceDiscounts::EVENT_DISCOUNT_MATCHES_ORDER,
            function(MatchOrderEvent $event) {
                // @var Discount $discount
                $discount = $event->discount;
                
                if ($discount->getType()) {
                    $event->isValid = false;
                }
            }
        );*/
        
        
        if ($this->getSettings()->multiCouponCodes) {
            
            Event::on(
                Order::class, 
                Order::EVENT_BEFORE_SAVE, 
                function(ModelEvent $event) {
                    $order = $event->sender;
                    $request = Craft::$app->getRequest();
                        
                    if ($couponCodes = $request->getParam('couponCodes')) {
                        $removeCodes = [];
                        foreach ($couponCodes as $key => $couponCode) {
                            if ($remove = $request->getParam("couponCodes.$key.remove", false)) {
                                $removeCodes[] = $key;
                            }
                        }
                        
                        if (count($removeCodes)) {
                            Db::delete('{{%commerce-enhanced-promotions_couponcodes}}', [
                                'orderId' => $order->id,
                                'code' => $removeCodes,
                            ]);
                        }
                    }
                    
                    
                    if ($order->couponCode) {
                        if ($discount = $this::getInstance()->discounts->isValidCode($order->couponCode)) {
                            
                            Db::upsert('{{%commerce-enhanced-promotions_couponcodes}}',
                            [
                                'code' => $order->couponCode,
                                'discountId' => $discount->id,
                                'orderId' => $order->id,
                            ], false);
                        }
                    }
                    
                    $order->couponCode = null;
                }
            );
            
        }
        
        Event::on(
            Response::class, 
            Response::EVENT_BEFORE_SEND, 
            function(Event $event) {
                if (($event->sender->template ?? null) == 'commerce/promotions/discounts/_edit') {
                    $event->sender->template = 'commerce-enhanced-promotions/discounts/_edit';
                }
            }
        );
        
        
        /*Craft::$app->view->hook("cp.commerce.order.edit.details", function(array &$context) {
            $view = Craft::$app->getView();
            $order = $context["order"];
        
            return $view->renderTemplate(
                "commerce-enhanced-promotions/discounts/_meta",
                ["order" => $order]
            );
        });*/
    }
}
