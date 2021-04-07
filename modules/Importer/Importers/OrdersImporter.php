<?php


namespace modules\Importer\Importers;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\web\User;
use modules\Importer\Components\S3SpreadSheetImporter;
use modules\Importer\Contracts\Importer;
use craft\commerce\Plugin as Commerce;
use modules\Orders\Models\OrderModel;
use modules\Orders\Models\ProductModel;
use modules\Orders\Models\Records\OrderRecord;
use modules\Orders\Models\Records\ProductRecord;
use modules\Orders\OrdersModule;

/**
 * Import orders from yalumba batch file. Order contents comes from LineItemsImporter. You
 * should run LineItemsImporter after running this.
 *
 * Class OrdersImporter
 * @package modules\Importer\Importers
 */
class OrdersImporter extends S3SpreadSheetImporter implements Importer
{

    protected static $importerIdentifier = 'orders';

    protected $path = 'orderHeader.csv';
    protected $record = OrderRecord::class;
    protected $skipFirst = true;
    protected $fields = [
        0 => 'orderNumber',
        1 => 'accountNumber',
        2 => 'accountName',
        3 => 'orderDate',
        4 => 'orderId', //customer order number
        6 => 'status'
    ];

    public function get_orderDate($row)
    {
        return DateTimeHelper::toDateTime(\DateTime::createFromFormat('d/m/Y', $row[3]));
    }

    public function get_accountNumber($row)
    {
        $user = \craft\elements\User::find()->yalumbaCustomerId($row[1])->one();
        if ($user) {
            $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
            return $customer->id;
        }
        return '';
    }

    public function getIdentifier($row)
    {
        return $row[0];
    }

    public function after_row_save($orderModel)
    {
        /*
 * Chanda - Order Id which was being pointed and used to update order status isn't being sent by client and will not be sent either
 * Hence, to resolve this - using Yalumba Order Number to point the craft's order and updating status
 */
        $records = OrderRecord::find()->select('orderId')->where(['orderNumber' => $orderModel->orderNumber])->one();
        //update craft order status:

        //chanda - check if the object found and then try to access it
        if ($records && $records->orderId) {
            $order = Commerce::getInstance()->getOrders()->getOrderByNumber($records->orderId);
            /*
             * Chanda - created new function under order status service to get the craftstatusid by yalumba status
             * calling craft's saveElement function to save updates in craft commerce order plugin element
             */
            if ($order && $order->id) {
                $orderStatusId = OrdersModule::getInstance()->orderStatus->getCraftStatusIdFromYalumbaStatus($orderModel->status);
                $order->orderStatusId = $orderStatusId;
                $order->message = "Update order status to " . $orderModel->status; // new message
                // Chanda - will print error if there will be any while updating status
                try {
                    Craft::$app->getElements()->saveElement($order, false, true, false);
                    $craftHandle = OrdersModule::getInstance()->orderStatus->getCraftStatusFromYalumbaStatus($orderModel->status);
                    $this->log("status updated of Number #" . $orderModel->orderNumber . " TO " . $craftHandle, 'Order');
                } catch (\Exception $ex) {
                    $this->log($ex->getMessage(), 'Order ' . $orderModel->orderNumber);
                }
            }
        } else {
            // Chanda - log for not found orders
            $this->log("Not found Order Number #" . $orderModel->orderNumber . " thus cannot update status for this.", 'Order');
        }
    }
}
