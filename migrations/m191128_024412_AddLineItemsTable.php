<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m191128_024412_AddLineItemsTable migration.
 */
class m191128_024412_AddLineItemsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp() {
	    $this->dropTableIfExists('{{%yalumba_line_items}}');
	    $this->createTable('{{%yalumba_line_items}}', [
		    'id'=>Schema::TYPE_PK,
			'orderNumber'=>Schema::TYPE_STRING,
			'lineNumber'=>Schema::TYPE_INTEGER,
			'phonetic'=>Schema::TYPE_STRING,
			'productName'=>Schema::TYPE_STRING,
			'quantity'=>Schema::TYPE_INTEGER,
			'uom'=>Schema::TYPE_STRING,
			'price'=>Schema::TYPE_FLOAT,
			'status'=>Schema::TYPE_STRING,
			'dateUpdated'=>Schema::TYPE_DATETIME,
			'dateCreated'=>Schema::TYPE_DATETIME,
			'uid'=>Schema::TYPE_STRING,
			'identifier'=>Schema::TYPE_STRING,
	    ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown() {
	    $this->dropTableIfExists('{{%yalumba_line_items}}');
    }
}
