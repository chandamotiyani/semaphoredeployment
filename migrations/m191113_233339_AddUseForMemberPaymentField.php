<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m191113_233339_AddUseForMemberPaymentField migration.
 */
class m191113_233339_AddUseForMemberPaymentField extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->addColumn('{{%commerce_paymentsources}}','useForMemberPayment', $this->boolean());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    	$this->dropColumn('{{%commerce_paymentsources}}', 'useForMemberPayment');
        return false;
    }
}
