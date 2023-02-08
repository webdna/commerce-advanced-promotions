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
use craft\commerce\services\Discounts as CommerceDiscounts;
use craft\commerce\models\Discount as CommerceDiscount;
use craft\commerce\services\OrderAdjustments;
use craft\events\DefineBehaviorsEvent;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ElementHelper;
use craft\helpers\Db;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\Response;
use webdna\commerce\enhancedpromotions\adjusters\Discount as DiscountAdjuster;
use webdna\commerce\enhancedpromotions\behaviors\OrderBehavior;
use webdna\commerce\enhancedpromotions\fields\Discounts as DiscountsAlias;
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
    public bool $hasCpSettings = false;
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
            // ...
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
                $event->types[] = DiscountsAlias::class;
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
            OrderAdjustments::class, 
            OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, 
            function(RegisterComponentTypesEvent $e) {
                $e->types[] = DiscountAdjuster::class;
            }
        );
        
        /*Event::on(
            CommerceDiscounts::class,
            CommerceDiscounts::EVENT_AFTER_SAVE_DISCOUNT,
            function(DiscountEvent $event) {
                // @var Discount $discount
                $discount = $event->discount;
                // @var bool $isNew
                $isNew = $event->isNew;
    
                $this->discounts->afterSaveDiscount($discount);
            }
        );*/
        
        /*Event::on(
            CommerceDiscounts::class,
            CommerceDiscounts::EVENT_AFTER_DELETE_DISCOUNT,
            function(DiscountEvent $event) {
                // @var Discount $discount
                $discount = $event->discount;
        
                $this->discounts->afterDeleteDiscount($discount);
            }
        );*/
        
        Event::on(
            Order::class, 
            Order::EVENT_BEFORE_SAVE, 
            function(ModelEvent $event) {
                $order = $event->sender;
                $request = Craft::$app->getRequest();
                    
                //Craft::dd($request->getParam('couponCodes'));
                
                $query = (new \craft\db\Query())
                    ->where([
                        'orderId' => $order->id,
                    ])
                    ->andWhere([
                        'not', ['code' => $request->getParam('couponCodes')],
                    ]);
                //Craft::dd($query->where);
                
                if ($codes = $request->getParam('couponCodes', null)) {
                    Db::delete('{{%commerce-enhanced-promotions_couponcodes}}', $query->where);
                }
        
                if ($order->couponCode) {
                    $discount = Commerce::getInstance()->getDiscounts()->getDiscountByCode($order->couponCode);
                        
                    Db::upsert('{{%commerce-enhanced-promotions_couponcodes}}',
                    [
                        'code' => $order->couponCode,
                        'discountId' => $discount->id,
                        'orderId' => $order->id,
                    ], false);
                }
                
                $order->couponCode = null;
            }
        );
        
        Event::on(
            Response::class, 
            Response::EVENT_BEFORE_SEND, 
            function(Event $event) {
                if ($event->sender->template == 'commerce/promotions/discounts/_edit') {
                    $event->sender->template = 'commerce-enhanced-promotions/discounts/_edit';
                }
            }
        );
    }
}
