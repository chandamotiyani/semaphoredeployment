<?php
namespace verbb\wishlist\services;

use verbb\wishlist\Wishlist;
use verbb\wishlist\elements\Item;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\events\ElementEvent;
use craft\events\SiteEvent;
use craft\queue\jobs\ResaveElements;

use yii\web\UserEvent;

class Items extends Component
{
    // Public Methods
    // =========================================================================

    public function getItemById(int $id, $siteId = null)
    {
        return Craft::$app->getElements()->getElementById($id, Item::class, $siteId);
    }

    public function getItemsForList(int $listId, $siteId = null)
    {
        return Item::find()
            ->listId($listId)
            ->status(null)
            ->all();
    }

    public function deleteItemsForList(int $listId, $siteId = null)
    {
        $items = Item::find()
            ->listId($listId)
            ->status(null)
            ->ids();

        foreach ($items as $itemId) {
            Craft::$app->getElements()->deleteElementById($itemId);
        }

        return true;
    }

    public function createItem($elementId, $listId, $listTypeId = null, $forceSave = false)
    {
        // null `listId` is okay - a new list will get created.
        if (!$elementId) {
            return null;
        }

        $list = Wishlist::$plugin->getLists()->getList($listId, $forceSave, $listTypeId);
        $element = Craft::$app->getElements()->getElementById((int)$elementId);

        if (!$element || !$list) {
            return null;
        }

        $item = new Item();
        $item->listId = $list->id;
        $item->elementId = $element->id;
        $item->elementSiteId = $element->siteId;
        $item->elementClass = get_class($element);

        $item->setFieldValuesFromRequest('fields');

        return $item;
    }

    public function saveElement(ElementInterface $element, bool $runValidation = true, bool $propagate = true)
    {
        $updateItemSearchIndexes = Wishlist::$plugin->getSettings()->updateItemSearchIndexes;

        return Craft::$app->getElements()->saveElement($element, $runValidation, $propagate, $updateItemSearchIndexes);
    }
}