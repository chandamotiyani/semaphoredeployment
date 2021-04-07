<?php


namespace modules\Vend\Responses\Traits;


use modules\ApiLog\ApiLogModule;

trait HasLog
{
    public function log($name, $data, $elementId)
    {
        ApiLogModule::getInstance()->logger->log($name, $data, $elementId);
        //\Craft::getLogger()->log($message, \yii\log\Logger::LEVEL_TRACE, "yalumba_api");
    }
}