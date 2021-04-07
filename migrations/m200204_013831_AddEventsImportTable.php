<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\mysql\Schema;

/**
 * m200204_013831_AddEventsImportTable migration.
 */
class m200204_013831_AddEventsImportTable extends Migration
{
    /**
     * @inheritdoc
     */
	public function safeUp() {
		$this->dropTableIfExists('{{%yalumba_api_events}}');
		$this->createTable('{{%yalumba_api_events}}', [
			'id'=>Schema::TYPE_PK,
			'name'=>Schema::TYPE_STRING,
			'shortDescription'=>Schema::TYPE_MEDIUMTEXT,
			'description'=>Schema::TYPE_MEDIUMTEXT,
			'identifier'=>Schema::TYPE_STRING,
			'dateUpdated'=>Schema::TYPE_DATETIME,
			'dateCreated'=>Schema::TYPE_DATETIME,
			'uid'=>Schema::TYPE_STRING,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown() {
		$this->dropTableIfExists('{{%yalumba_api_events}}');
	}
}
