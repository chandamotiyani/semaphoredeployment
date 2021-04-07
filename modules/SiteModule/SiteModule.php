<?php
/**
 * Site module for Craft CMS 3.x
 *
 * An example module for Craft CMS 3 that lets you enhance your websites with a custom site module
 */

namespace modules\SiteModule;

use Craft;
use craft\commerce\events\AddressEvent;
use craft\commerce\Plugin as Commerce;
use craft\commerce\services\Addresses;
use craft\commerce\services\Carts;
use craft\commerce\services\Customers;
use craft\console\Application as ConsoleApplication;
use craft\events\DefineRulesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\View;
use craft\web\UrlManager;
use modules\Memberships\MembershipModule;
use yii\base\Event;
use yii\base\Module;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use modules\SiteModule\Services\ProductService;
use craft\commerce\elements\Variant;
use craft\commerce\elements\Order;


use craft\events\ElementEvent;
use craft\helpers\ElementHelper;
use craft\services\Elements;

use craft\commerce\models\LineItem;
use craft\commerce\events\LineItemEvent;
use craft\commerce\services\LineItems;
use craft\commerce\models\Address;
use craft\commerce\models\Cart;
use craft\elements\User;
use verbb\wishlist\Wishlist;
use verbb\wishlist\elements\Item;
use yii\web\User as WebUser;
use yii\web\UserEvent;

use craft\events\ModelEvent;
use craft\helpers\DateTimeHelper;


/**
 * Class SiteModule
 *
 */
class SiteModule extends Module
{
    // Static Properties
    // =========================================================================

    /**
     * @var SiteModule
     */
    public static $instance;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {

        Craft::setAlias('@modules/SiteModule', $this->getBasePath());
        $this->controllerNamespace = 'modules\SiteModule\Controllers';

        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = WineType::class;
        });

        // Set this as the global instance of this module class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {

                if (Craft::$app->getRequest()->getIsConsoleRequest()) {
                    return;
                }


                /**
                 * GDPR Cookies
                 */
                $gdpr = Craft::$app->getRequest()->getParam('gdpr');
                // Create cookie object.

                if ($gdpr !== NULL) {
                    $cookie = Craft::createObject([
                        'class' => 'yii\web\Cookie',
                        'name' => 'gdpr',
                        'httpOnly' => false,
                        'value' => (bool)$gdpr,
                        'expire' => time() + (86400 * 365),
                    ]);
                    Craft::$app->getResponse()->getCookies()->add($cookie);

                    $redirect = Craft::$app->getRequest()->getParam('redirect');

                    if (strpos($redirect, Craft::$app->getRequest()->getHostName())) {
                        header('Location: ' . $redirect);
                    }
                }
            }
        );


        $this->setComponents([
            'product_query' => ProductService::class,
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $e) {
            /** @var CraftVariable $variable */
            $variable = $e->sender;

            // Attach a service:
            $variable->set('productService', ProductService::class);
        });

        Event::on(Variant::class, Variant::EVENT_AFTER_CAPTURE_PRODUCT_SNAPSHOT, function (Event $e) {
            $product = $e->product;
            $data = $e->fieldData;

            // Get the image - this is madness
            if (!empty($e->product->defaultVariant->productImageFront[0])) {
                $data['image'] = $e->product->defaultVariant->productImageFront->first()->getUrl();
            }

            if (!empty($e->product->productinfogift[0])) {
                $data['image'] = $e->product->productinfogift->first()->productImageFront->first()->getUrl();
            }

            if (!empty($e->product->productInfoMerchandise[0])) {
                $data['image'] = $e->product->productInfoMerchandise->first()->productImageFront->first()->getUrl();
            }
            if (!empty($e->product->defaultVariant->giftOptions)) {
                $hasGiftOptions = isset($e->product->defaultVariant->giftOptions->one()->id);
                $data['has_gift_options'] = $hasGiftOptions;
            }

            if (!empty($e->product->type->handle)) {
                $data['handle'] = $e->product->type->handle;
            }

            // Add gift option price
            $e->fieldData = $data;
        });


        Event::on(Variant::class, Variant::EVENT_AFTER_CAPTURE_VARIANT_SNAPSHOT, function (Event $e) {
            $data = $e->fieldData;
            if (!empty($e->variant->bottleSizes) && !empty($e->variant->bottleSizes->one())) {
                $data['bottleSize'] = $e->variant->bottleSizes->one()->title;
            }

            $e->fieldData = $data;
        });

        /**
         * TODO: @Simon. Does this belong somewhere else?
         * Restrict product purchases on products with permissions.
         */
        Event::on(LineItem::class, LineItem::EVENT_BEFORE_VALIDATE, function (Event $e) {
            // Chanda - webmaster account issue
            $memberPermission = [];
            if (isset($e->sender->purchasable->event)) {
                $memberPermission = $e->sender->purchasable->event->memberPermission;
            } elseif (isset($e->sender->purchasable->product)) {
                $memberPermission = $e->sender->purchasable->product->memberPermission;
            }

            if (!empty($memberPermission)) {
                /*$memberPermission = isset($e->sender->purchasable->event) ?
            @$e->sender->purchasable->event->memberPermission :
            $memberPermission = @$e->sender->purchasable->product->memberPermission;*/

                //GET THE USERS PERMISSIONS
                $user = Craft::$app->user;
                $userPermissions = [];
                if (Craft::$app->user->getId()) {
                    $userPermissions = Craft::$app->getUserPermissions()->getPermissionsByUserId($user->getId());
                }

                //GET THE PRODUCTS PERMISSIONS AND MATCH THOSE AGAINST USER
                $hasPermission = array_filter((array)$memberPermission, function ($option) use ($userPermissions) {
                    if ($option->value == 'guest') {
                        return true;
                    }

                    if (in_array('view-' . $option->value, $userPermissions)) {
                        return true;
                    }
                });


                // THEY DON'T HAVE PERMISSIONS TO PURCHASE. ADD ERROR MESSAGE TO LINEITEM
                if ($hasPermission == false) {
                    $permissions = $this->get_selected_permission($e->sender->purchasable->product->memberPermission);
                    if (!in_array('Insider', $permissions)) {
                        $e->sender->addError('noInsider', 'If you are a ' . implode(" AND OR ", $permissions) . ', please log in. This is only available to certain Members.');
                        $e->isValid = false;
                    } else {
                        $e->sender->addError('memberOnly', 'You must be a ' . implode(" OR ", $permissions) . ' to purchase ' . $e->sender->purchasable->title . '. Please login as a member to continue.');
                        $e->isValid = false;
                    }


                    // Save the details of the item user is trying to buy in session - we'll add it to cart when signed in.
                    //Craft::$app->getSession()->set('purchasableId', $e->sender->purchasable->id);
                }


                // Chanda - 15th Jan 2021  Adding validation before product gets added to the cart for the coming soon category to fix the problem of the cart which is apearing empty
                if (@isset($e->sender->purchasable->product->availableForPurchase) && $e->sender->purchasable->product->availableForPurchase <= 0) {
                    $e->sender->addError('comingSoon', $e->sender->purchasable->title . ' IS CURRENTLY UNAVAILABLE.');
                    $e->isValid = false;
                }
            }

        });

        Event::on(Address::class, Address::EVENT_DEFINE_RULES, function (DefineRulesEvent $event) {
            $event->rules[] = [['address1'], 'required'];
            $event->rules[] = [['city'], 'required'];
            $event->rules[] = [['stateId'], 'required'];
            $event->rules[] = [['zipCode'], 'required'];
            $event->rules[] = [['firstName'], 'required'];
            $event->rules[] = [['lastName'], 'required'];
            $event->rules[] = [['countryId'], 'required'];
        });

        /**
         * Exclude Postcodes and address validation
         */
        Event::on(Address::class, Address::EVENT_DEFINE_RULES, function (DefineRulesEvent $event) {
            $cart = $event->sender;
            if (!Craft::$app instanceof ConsoleApplication) {
                $request = Craft::$app->getRequest()->getBodyParams();

                if (!empty($request['shippingAddress'])) {
                    // Get Excluded postcode names from Config.
                    $excludedPostcodes = \Craft::$app->getConfig()->getConfigFromFile('commerce')['excludedPostcodes'];

                    if (!empty($request['shippingAddress']['zipCode']) && in_array($request['shippingAddress']['zipCode'], $excludedPostcodes)) {
                        $cart->addError('zipCode', 'Sorry, we don\'t ship to this Post Code');
                        //$cart->isValid = false;
                    }


                    if ((!empty($request['shippingAddress']['address1']) || !empty($request['billingAddress']['address1'])) && strlen($cart->address1) > 40) {
                        $cart->addError('address1', 'Address must be 40 characters or under');
                    }

                }
            }
        });

        Event::on(Order::class, Order::EVENT_BEFORE_COMPLETE_ORDER, function (Event $e) {
            // @var Order $order
            $order = $e->sender;
            $user = $order->user;

            if ($user) {

                if (isset($order->firstName)) {
                    $order->setFieldValue("firstName", isset($user->firstName) ? $user->firstName : '');
                }
                if (isset($order->lastName)) {
                    $order->setFieldValue("lastName", isset($user->lastName) ? $user->lastName : '');
                }
                if (isset($order->dateOfBirth)) {
                    $order->setFieldValue("dateOfBirth", isset($user->dateOfBirth) ? $user->dateOfBirth : '');
                }

                /**
                 * Save users phone number if updated in checkout.
                 */
                if (isset($order->phoneNumber) && $order->phoneNumber !== $user->phoneNumber) {
                    $user->phoneNumber = $order->phoneNumber;
                    Craft::$app->getElements()->saveElement($user);
                }


            }

            // if the shipping address details change, remove the shipping address ID, but keep prefilll.


            // ...
        });


        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->siteRoutes());
            }
        );


        Event::on(LineItems::class, LineItems::EVENT_POPULATE_LINE_ITEM, function (LineItemEvent $e) {

            $options = $e->lineItem->options;
            foreach ($e->lineItem->options as $productID => $productTitle) {

                if ($productTitle == "false") {
                    unset($options[$productID]);
                }
            }

            $e->lineItem->options = $options;


            // order the line items.

            /*
                $data = $e->fieldData;
                if(! empty($e->variant->bottleSizes) && ! empty($e->variant->bottleSizes->one())) {
                    $data['bottleSize'] = $e->variant->bottleSizes->one()->title;
                }

                $e->fieldData = $data;
                */
        });

        Event::on(WebUser::class, WebUser::EVENT_AFTER_LOGOUT, function (UserEvent $event) {

            /*
             * Chanda Vyas - 29th July
             * START - FLUSH CART POST LOGOUT
             */
            $cart_instance = new Carts();
            $cart_instance->forgetCart();

            $customer_instance = new Customers();
            $customer_instance->forgetCustomer();
            /*
             * END - FLUSH CART POST LOGOUT
             */
            
            Craft::$app->response->redirect('/the-wine-club');

            Craft::$app->end();
        }, null, false);

        Event::on(Order::class, Order::EVENT_BEFORE_VALIDATE, function (\yii\base\ModelEvent $event) {

            if ($event->sender->email) {

                // we need to check the dns of email addresses - if rezdy detects that an email
                // address is invalid it throws it's own errors - which means that events can't
                // be purchased.
                if (!filter_var($event->sender->email, FILTER_VALIDATE_EMAIL) || !MembershipModule::getInstance()->members->isValidEmail($event->sender->email)) {
                    $event->sender->addError('email', 'Not a valid email address' . $event->sender->email);
                    $event->isValid = false;
                }
            }
        });

        Event::on(User::class, User::EVENT_BEFORE_SAVE, function (ModelEvent $event) {


            if (empty($event->sender->firstName)) {
                $event->sender->addError('firstName', 'First Name is required');
                $event->isValid = false;
            }

            if (empty($event->sender->lastName)) {
                $event->sender->addError('lastName', 'Last Name is required');
                $event->isValid = false;
            }

            if (!$event->sender->dateOfBirth) {
                $event->sender->addError('fields[dateOfBirth]', 'Date of birth is required');
                $event->isValid = false;
            }

            // we need to check the dns of email addresses - if rezdy detects that an email
            // address is invalid it throws it's own errors - which means that events can't
            // be purchased.
            if (!MembershipModule::getInstance()->members->isValidEmail($event->sender->email)) {
                $event->sender->addError('email', 'Not a valid email address');
                $event->isValid = false;
            }

            if ($event->sender->dateOfBirth) {

                if ($this->isInvalidDOB($event->sender->dateOfBirth)) {
                    $event->sender->addError('fields[dateOfBirth]', $this->isInvalidDOB($event->sender->dateOfBirth));
                    $event->isValid = false;
                }
            }

            if (strlen($event->sender->phoneNumber) > 20) {
                $event->sender->addError('fields[phoneNumber]', 'Phone Number is invalid');
                $event->isValid = false;
            }
        });

    }

    public function siteRoutes()
    {
        return [
            '/products/wishlist' => 'site-module/site-module/wishlist',
            '/products/products' => 'site-module/site-module/products',
            '/products/remove-from-wishlist' => 'site-module/site-module/remove-from-wishlist',
            '/products/variants/<variantId:\d+>' => 'site-module/site-module/variants',
            '/products/gift-options/<productId:\d+>/<lineItemId:\d+>' => 'site-module/site-module/gift-options',
            '/products/gift-options/<productId:\d+>' => 'site-module/site-module/gift-options',
            '/members/remove-account' => 'site-module/site-module/remove-account',
            '/members/create-wine-room-user' => 'site-module/site-module/create-wine-room-user',
            '/members/get-user-group' => 'site-module/site-module/get-user-group',
        ];
    }

    public function validateZipCode($attribute, $params, $validator)
    {
        $this->addError('zipCode', Craft::t('commerce', 'Invalid Business Tax ID.'));
    }

    private function isInvalidDOB($dob)
    {
        if (is_object($dob) || \DateTime::createFromFormat("d/m/Y", $dob)) {

            if (DateTimeHelper::isWithinLast($dob, '18 years')) {
                return 'You must be at least 18 years old to register';
            }

            if (!DateTimeHelper::isWithinLast($dob, '120 years')) {
                return 'Date of birth is invalid';
            }

            if (!DateTimeHelper::isInThePast($dob)) {
                return 'Date of birth is in the future';
            }

            return false;
        }
    }

    /*
     * will get the products members permissions and prompt in message when user will try to add them in cart without having those permissions
     */
    public function get_selected_permission($obj)
    {
        $permissions = [];
        if (!empty($obj)) {
            foreach ($obj as $key => $value) {
                array_push($permissions, $value->label);
            }
        }
        return $permissions;
    }

}
