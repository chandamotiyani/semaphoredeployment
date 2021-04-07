<?php

namespace modules\SiteModule\Controllers;


use Craft;
use craft\web\Controller;
use craft\elements\User;
use verbb\wishlist\Wishlist;
use verbb\wishlist\elements\db\ItemQuery;
use verbb\wishlist\elements\db\ListQuery;
use verbb\wishlist\elements\Item;
use verbb\wishlist\elements\ListElement;
use modules\SiteModule\SiteModule;
use craft\commerce\elements\Product;

class SiteModuleController extends Controller
{

    //protected $allowAnonymous = ['wishlist', 'products', 'variants', 'remove-from-wishlist'];

    protected $allowAnonymous = true;

    //TODO, changed get products to api response. This will need future refactoring so query params are picked up here instead of in the Service.
    public function actionProducts()
    {
        $productType = Craft::$app->request->getQueryParam('productType');

        $filter = explode(',', $productType);

        $wines = SiteModule::getInstance()->product_query->filterBy($filter)->all();

        $out = [];
        foreach ($wines as $wine) {
            $out[] = $wine->id;
        }

        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $out;
    }

    public function actionVariants($variantId)
    {

        $product = \craft\commerce\elements\Variant::find()->id($variantId)->one();

        $out = [];
        $out[] = [ //element.defaultVariant.price
            'price' => !empty($product->getSalePrice()) ? '$' . $product->getSalePrice() : '',
            'image' => SiteModule::getInstance()->product_query->getImageUrl($product),
        ];


        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return $out;
    }

    public function actionWishlist()
    {
        $list = Wishlist::$plugin->getLists()->getList(null, true);

        $items = Item::find()->listId($list->id)->all();
        $out = [];
        foreach ($items as $item) {

            if (!empty($item->element->defaultVariant)) {
                $element = $item->element->defaultVariant;
            } else {
                $element = $item->element;
            }

            $out[] = [ //element.defaultVariant.price
                'id' => $item->element->id,
                'purchasableId' => $element->id,
                'price' => !empty($element->getSalePrice()) ? \Craft::$app->getFormatter()->asCurrency($element->salePrice, 'AUD', [], [], true) : '',
                'imageUrl' => SiteModule::getInstance()->product_query->getImageUrl($element),
                'title' => $element->title,
                'removeUrl' => $item->removeUrl,
                'listId' => $list->id,
                'disableBuyButton' => ($element->getIsAvailable() == false) or ($element->stock <= 0 and $element->hasUnlimitedStock == false),
                /* Chanda - Link product with the detail page of it */
                'productUrl' => @$element->url,
                /* End */
            ];
        }

        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $out;
    }


    public function actionRemoveFromWishlist()
    {
        $request = Craft::$app->getRequest();
        $elementId = $request->getParam('elementId');
        $listId = $request->getParam('listId');

        $listTypeId = $request->getParam('listTypeId');
        $listTypeHandle = $request->getParam('listTypeHandle');

        //Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$listTypeId && $listTypeHandle) {
            // Always take the ID first. If both are sent, Handle is ignored.
            $listType = WishList::$plugin->getListTypes()->getListTypeByHandle($listTypeHandle);

            if ($listType) {
                $listTypeId = $listType->id;
            }
        }

        $list = Wishlist::$plugin->getLists()->getList($listId, true, $listTypeId);

        if (!$elementId) {
            return 'error';
        }

        $items = Item::find()
            ->elementId($elementId)
            ->listId($list->id)
            ->all();

        if (!$items) {
            return 'error';
        }

        foreach ($items as $item) {
            if (!Craft::$app->getElements()->deleteElement($item)) {
                return 'error';
            }
        }

        return 'success';
    }

    public function actionGiftOptions($productId, $lineItemId = null)
    {


        $products = SiteModule::getInstance()->product_query->getGiftOptions($productId, $lineItemId);

        $request = Craft::$app->getRequest();
        $purchasableId = $request->getParam('purchasableId');

        if ($purchasableId) {
            $products['purchasableId'] = $purchasableId;
        }

        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $products;
    }

    /**
     * Allows user to delete their membership.
     * TODO: This route isn't working when using the action param.
     */
    public function actionRemoveAccount()
    {

        $this->requirePostRequest();

        $currentUser = Craft::$app->getUser();

        Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (empty($currentUser->id)) {
            return ['error' => 'not allowed'];
        }

        // Don't allow admin users to delete accounts.
        if ($currentUser->getIsAdmin()) {
            return ['error' => 'Admins can\'t delete accounts'];
        }

        if (Craft::$app->getElements()->deleteElementById($currentUser->id, 'craft\elements\User')) {
            return ['success' => 'Account deleted'];
        }

    }

    public function actionCreateWineRoomUser()
    {

        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $user = new User();
        $user->email = $request->getParam('email');
        $user->username = $user->email;
        $user->firstName = $request->getParam('firstName');
        $user->lastName = $request->getParam('lastName');
        $user->state = isset($request->getParam('fields')['state']) ? $request->getParam('fields')['state'] : '';
        $user->password = $request->getParam('password');
        $user->dateOfBirth = isset($request->getParam('fields')['dateOfBirth']) ? $request->getParam('fields')['dateOfBirth'] : '';

        if (Craft::$app->elements->saveElement($user)) {

            Craft::$app->getUsers()->activateUser($user);

            return $this->asJson([
                'success' => true,
                'id' => $user->id
            ]);
        }

        return $this->asJson([
            'errors' => $user->getErrors(),
        ]);
    }

    /*
     * This will perform search site wide for the $q denotes keyword searched by user
     * Created By - Chanda on 15th July 2020
     * */


}