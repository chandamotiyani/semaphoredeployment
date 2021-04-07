<?php


namespace modules\Yalumba\Jobs;


use craft\commerce\elements\Order;
use craft\commerce\Plugin as Commerce;
use craft\queue\JobInterface;
use craft\queue\QueueInterface;
use Exception;
use modules\Orders\Models\OrderModel;
use modules\Orders\OrdersModule;
use modules\Yalumba\Errors\YalumbaException;
use modules\Yalumba\Responses\Traits\HasLog;
use modules\Yalumba\YalumbaApiModule;
use yii\base\BaseObject;
use \Craft;

/**
 * Class SendOrderJob
 * @package modules\Orders\Jobs
 *
 * @property null|string $description
 */
class SendOrderJob extends BaseObject implements JobInterface {

    use HasLog;

	public $orderId;
    public $orderUid;

	/**
	 * @return string|null
	 */
	public function getDescription() {
		return "Send Successful Order to Yalumba API";
	}

    /**
     * @param QueueInterface|\yii\queue\Queue $queue
     *
     * @throws YalumbaException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
	public function execute( $queue ) {
		$order = Commerce::getInstance()->orders->getOrderById($this->orderId);
		if(!$order->yalumbaOrderNumber){
            $response = YalumbaApiModule::getInstance()->api->sendYalumbaOrder($order);
            if($response->isSuccessful()){
                $orderNumber = $response->getData()->orderNumber;
                //we grab the order again incase it's been updated from some other source.
                $order = Commerce::getInstance()->orders->getOrderById($this->orderId);
                //save into Craft Order:
                $order->setFieldValue('yalumbaOrderNumber', $orderNumber);
                $saveResult = Craft::$app->elements->saveElement($order, false, true, false);
                if(!$saveResult){
                    $this->log("save yalumba order", json_encode(["could not save"]), $order->id);
                }
                else{
                    $this->log("save yalumba order", json_encode(["saved"]), $order->id);
                }
                //and save it into the yalumba order:
                $yalumbaOrder = OrdersModule::getInstance()->orders->getYalumbaOrder($order->number);
                $yalumbaOrder->orderNumber = $orderNumber;
                $yalumbaOrder->identifier = $orderNumber;
                $yalumbaOrder = OrdersModule::getInstance()->orders->pushYalumbaOrderRecord($yalumbaOrder);
            }
            else{
                throw new YalumbaException('Yalumba API data send failed - Response code '.$response->getResponseCode() . ' received: '.$response->response->getBody());
            }
        }
	}
}