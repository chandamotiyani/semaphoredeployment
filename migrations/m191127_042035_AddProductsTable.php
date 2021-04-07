<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m191127_042035_AddProductsTable migration.
 */
class m191127_042035_AddProductsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropTableIfExists('{{%yalumba_products}}');
	    $this->createTable('{{%yalumba_products}}', [
		    'id' => Schema::TYPE_PK,
		    'dateCreated'=>Schema::TYPE_DATETIME,
		    'dateUpdated'=>Schema::TYPE_DATETIME,
		    'dateDeleted'=>Schema::TYPE_DATETIME,
		    'uid'=>Schema::TYPE_STRING,
			'phonetic'=>Schema::TYPE_STRING,
			'primaryUnit'=>Schema::TYPE_STRING,
			'primaryUOMAllowSale'=>Schema::TYPE_STRING,
			'secondaryUnit'=>Schema::TYPE_STRING,
			'secondaryUOMAllowSale'=>Schema::TYPE_STRING,
			'primaryUnitPackSize'=>Schema::TYPE_STRING,
			'primaryUnitsPerSecondary'=>Schema::TYPE_STRING,
			'salesRangeKey'=>Schema::TYPE_STRING,
			'salesRangeName'=>Schema::TYPE_STRING,
			'brandKey'=>Schema::TYPE_STRING,
			'brandName'=>Schema::TYPE_STRING,
			'name'=>Schema::TYPE_STRING,
			'nameShort'=>Schema::TYPE_STRING,
			'pH'=>Schema::TYPE_STRING,
			'totalAcid'=>Schema::TYPE_STRING,
			'alcoholByVolume'=>Schema::TYPE_STRING,
			'region'=>Schema::TYPE_STRING,
			'productType'=>Schema::TYPE_STRING,
			'varietal'=>Schema::TYPE_STRING,
			'vintage'=>Schema::TYPE_STRING,
			'closure'=>Schema::TYPE_STRING,
			'harvestDates'=>Schema::TYPE_STRING,
			'mediumImageUrl'=>Schema::TYPE_STRING,
			'smallImageUrl'=>Schema::TYPE_STRING,
			'brandImageUrl'=>Schema::TYPE_STRING,
			'tastingNoteUrl'=>Schema::TYPE_STRING,
			'identifier'=>Schema::TYPE_STRING,
		    'paymentCurrency'=>Schema::TYPE_STRING,
	    	]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
	    $this->dropTableIfExists('{{%subscription_yalumba_products}}');
    }
}
