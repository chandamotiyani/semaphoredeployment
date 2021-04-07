<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use yii\db\Schema;

/**
 * m191128_024402_AddOrdersTable migration.
 */
class m191128_024402_AddOrdersTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropTableIfExists('{{%yalumba_orders}}');
	    $this->createTable('{{%yalumba_orders}}', [
		    'id'=>Schema::TYPE_PK,
			'orderNumber'=>Schema::TYPE_STRING,
			'accountNumber'=>Schema::TYPE_STRING,
			'accountName'=>Schema::TYPE_STRING,
			'orderDate'=>Schema::TYPE_DATE,
			'orderId'=>Schema::TYPE_STRING,
			'status'=>Schema::TYPE_STRING,
			'dateCreated'=>Schema::TYPE_DATETIME,
			'dateUpdated'=>Schema::TYPE_DATETIME,
			'identifier'=>Schema::TYPE_STRING,
			'totalQuantity'=>Schema::TYPE_INTEGER,
			'totalPrice'=>Schema::TYPE_FLOAT,
			'uid'=>Schema::TYPE_STRING,
	    ]);
        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown() {
        $this->dropTableIfExists('{{%yalumba_orders}}');
    }
}
