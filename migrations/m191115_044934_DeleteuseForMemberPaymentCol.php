<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m191115_044934_DeleteuseForMemberPaymentCol migration.
 */
class m191115_044934_DeleteuseForMemberPaymentCol extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	    $this->dropColumn('{{%commerce_paymentsources}}', 'useForMemberPayment');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191115_044934_DeleteuseForMemberPaymentCol cannot be reverted.\n";
        return false;
    }
}
