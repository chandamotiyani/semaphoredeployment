<?php
namespace verbb\wishlist\base;

use verbb\wishlist\Wishlist;
use verbb\wishlist\services\Lists;
use verbb\wishlist\services\ListTypes;
use verbb\wishlist\services\Items;
use verbb\wishlist\services\Pdf;

use Craft;
use craft\log\FileTarget;

use yii\log\Logger;

use verbb\base\BaseHelper;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    public static $plugin;


    // Public Methods
    // =========================================================================

    public function getLists()
    {
        return $this->get('lists');
    }

    public function getListTypes()
    {
        return $this->get('listTypes');
    }

    public function getItems()
    {
        return $this->get('items');
    }

    public function getPdf()
    {
        return $this->get('pdf');
    }

    public static function log($message)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'wishlist');
    }

    public static function error($message)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'wishlist');
    }


    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'lists' => Lists::class,
            'listTypes' => ListTypes::class,
            'items' => Items::class,
            'pdf' => Pdf::class,
        ]);

        BaseHelper::registerModule();
    }

    private function _setLogging()
    {
        BaseHelper::setFileLogging('wishlist');
    }

}