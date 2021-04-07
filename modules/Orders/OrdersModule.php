<?php


namespace modules\Orders;


use craft\commerce\base\OrderValidatorsTrait;
use craft\commerce\db\Table;
use craft\commerce\elements\Order;
use craft\commerce\events\ProcessPaymentEvent;
use craft\commerce\events\RefundTransactionEvent;
use craft\commerce\models\Address;
use craft\commerce\services\Addresses;
use craft\commerce\services\Payments;
use craft\db\Query;
use craft\events\RegisterUrlRulesEvent;
use craft\mail\Message;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use modules\Events\EventsModule;
use modules\Orders\Services\OrderStatuses;
use modules\Yalumba\Jobs\SendOrderJob;
use modules\Orders\Models\OrderModel;
use modules\Orders\Services\Orders;
use craft\base\Element;
use craft\elements\User;
use craft\events\RegisterElementSearchableAttributesEvent;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\base\Module;
use craft\commerce\Plugin as Commerce;
use \Craft;

class OrdersModule extends Module
{

    /**
     * ImporterModule constructor.
     *
     * @param $id
     * @param null $parent
     * @param array $config
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/Orders', $this->getBasePath());
        $this->controllerNamespace = 'modules\Orders\Controllers';
        // Set this as the global instance of this module class
        static::setInstance($this);
        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        parent::init();

        $this->setComponents([
            'orders' => Orders::class,
            'orderStatus' => OrderStatuses::class,
        ]);

        // before any order is complete, we need to create bookings in rezdy and put them on hold.
//		Event::on(Order::class,  Order::EVENT_BEFORE_COMPLETE_ORDER, function(Event $e) {
//			$order = $e->sender;
//			EventsModule::getInstance()->purchase->hold($order);
//		});


        Event::on(Payments::class, Payments::EVENT_BEFORE_PROCESS_PAYMENT, function (ProcessPaymentEvent $e) {
            $this->notifyMultipleFailedAttempts($e->order->getTransactions(), $e->order);
            // Do something - maybe check if the shipment is really ready before capturing
            //$transaction = $e->transaction;
            $e->order = EventsModule::getInstance()->purchase->hold($e->order);

            if ($e->order->hasErrors()) {
                $e->isValid = false;
            }
        });


        /**
         * after processing the payment, if the payment fails, cancel the event booking.
         */


        Event::on(Payments::class, Payments::EVENT_AFTER_PROCESS_PAYMENT, function (ProcessPaymentEvent $e) {
            if ($e->order && (!$e->order->isCompleted || $e->transaction->status != 'success')) {
                //$e->transaction->
                // payment (or something) has failed we need to roll back the event purchases.
                // I don't want to do this from the queue since this this has to be done asap so
                // the customer can correct their payment and re-submit immediately..
                EventsModule::getInstance()->purchase->cancel($e->order);
            }
        });

        /**
         * after order is completed, make the custom yalumba order. This is used for a users past
         * orders screen and also on importing & syncing orders from Yalumba
         */
        Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, function (Event $e) {
            $order = $e->sender;
            $yalumbaOrder = new OrderModel();
            $yalumbaOrder->orderId = $order->number;
            $yalumbaOrder->identifier = $order->number;
            $yalumbaOrder->totalQuantity = $order->totalQty;
            $yalumbaOrder->totalPrice = $order->totalPrice;
            $yalumbaOrder->accountNumber = $order->customerId;
            $yalumbaOrder = self::getInstance()->orders->pushYalumbaOrderRecord($yalumbaOrder);
            //this creates a yalumba order record.
        });

        /**
         * after complete order, send the order to relevant parties (ie yalumba api)
         */
        Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, function (Event $e) {
            $order = $e->sender;
            Craft::$app->queue->delay(5)->push(new SendOrderJob(['orderId' => $order->id, 'orderUid' => $order->number]));
        });

        Event::on(
            User::class,
            Element::EVENT_REGISTER_SEARCHABLE_ATTRIBUTES,
            function (RegisterElementSearchableAttributesEvent $event) {
                $event->attributes[] = 'yalumbaCustomerId';
                $event->attributes[] = 'vendCustomerId';
            }
        );

        Event::on(
            Order::class,
            Element::EVENT_REGISTER_SEARCHABLE_ATTRIBUTES,
            function (RegisterElementSearchableAttributesEvent $event) {
                $event->attributes[] = 'yalumbaOrderNumber';
            }
        );

        /**
         * after order is completed, if stock levels are low, send low stock warning.
         */
        Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, function (Event $e) {
            $order = $e->sender;
            $threshold = Craft::$app->getConfig()->getConfigFromFile('commerce')['lowStockThreshold'];
            foreach ($order->lineItems as $lineItem) {

                $purchasable = $lineItem->purchasable;

                if (isset($purchasable->stock) && isset($purchasable->hasUnlimitedStock)) {
                    if ($purchasable->hasUnlimitedStock == false) {
                        if ($purchasable->stock <= $threshold) {
                            //TODO: mail function should be moved to a service for more re-use;
                            $mailer = Craft::$app->getMailer();
                            $view = Craft::$app->getView();
                            $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
                            $renderVariables = compact('lineItem');
                            /* Chanda - email can be sent to multiple recipients set in env variable */
                            $toEmail = getenv("LOW_STOCK_EMAIL");
                            $email = $toEmail ? explode(",", $toEmail) : "emmanuelle.harrington@kojo.com.au";
                            /* End */
                            $templatePath = 'emails/internal/stock_level_warning';
                            $body = $view->renderTemplate($templatePath, $renderVariables);
                            $message = (new Message())
                                ->setFrom($mailer->from)
                                ->setTo($email)
                                ->setReplyTo($mailer->from)
                                ->setSubject('Low Stock')
                                ->setTextBody("Low stock on item $purchasable->productId")
                                ->setHtmlBody($body);
                            $mailer->send($message);
                        }
                    }
                }
            }
        });

        /**
         * after a successful refund, send an email to the customer.
         */
        Event::on(Payments::class, Payments::EVENT_AFTER_REFUND_TRANSACTION, function (RefundTransactionEvent $e) {
            $order = $e->transaction->order;
            $transaction = $e->transaction;
            $note = $e->transaction->note;
            $amount = $e->transaction->amount;

            //TODO: mail function should be moved to a service for more re-use;
            $mailer = Craft::$app->getMailer();
            $view = Craft::$app->getView();
            $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
            $renderVariables = compact('order', 'note', 'amount', 'transaction');
            $templatePath = 'emails/customer/refunded';
            $body = $view->renderTemplate($templatePath, $renderVariables);

            $message = (new Message())
                ->setFrom($mailer->from)
                ->setTo($order->email)
                ->setReplyTo($mailer->from)
                ->setSubject('Refund')
                ->setTextBody("refund processed")
                ->setHtmlBody($body);
            $mailer->send($message);

        });

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->cpRoutes());
            }
        );

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->siteRoutes());
            }
        );

        /**
         * Attach the behaviours. These mostly make yalumba orders available for use in templates
         */
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $e) {
            /** @var CraftVariable $variable */
            $var = $e->sender;

            $behaviours = $e->sender->attachBehaviors([
                OrdersVariableBehavior::class,
                OrderStatusVariableBehavior::class
            ]);
        });

    }

    public function cpRoutes()
    {
        return [
//			'/importer/trigger-import' => 'importer/importer/trigger-import',
//			'/importer/orders' => 'importer/importer/orders'
        ];
    }

    public function siteRoutes()
    {
        return [
            '/customer/orders/external-order' => ''
//			'/importer/orders' => 'importer/importer/orders'
        ];
    }

    public function notifyMultipleFailedAttempts($all_transactions, $order) {
        if (!empty($all_transactions)) {
            $failed_transactions = 0;
            foreach ($all_transactions as $transaction) {
                if ($transaction->status == 'failed') {
                    $failed_transactions++;
                }
            }
            if ($failed_transactions > 7) {
                $mailer = Craft::$app->getMailer();
                $view = Craft::$app->getView();
                $toEmail = array("paul.sobolewski@kojo.com.au","emmanuelle.harrington@kojo.com.au");
                $message = (new Message())
                    ->setFrom($mailer->from)
                    ->setTo($toEmail)
                    ->setReplyTo($mailer->from)
                    ->setSubject('Multiple failed attempts against order id #' . $order->id)
                    ->setTextBody("Multiple transaction attempts has been made against order id #" . $order->id.", the total number of attempts till now are ".$failed_transactions)
                    ->setHtmlBody("Multiple transaction attempts has been made against order id #" . $order->id.", the total number of attempts till now are ".$failed_transactions);
                $mailer->send($message);
            }
        }
    }
}