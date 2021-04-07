<?php


namespace modules\ApiLog\Services;

use craft\base\Component;
use craft\db\Query;
use modules\ApiLog\Models\ApiLogModel;
use modules\ApiLog\Models\Records\ApiLogRecord;
use yii\db\ActiveQuery;

class ApiLog extends Component {

    private $_fetchedApiLogs;
    private $_fetchedApiByElement;
    private $_apiLogsById;


	/**
	 * Orders constructor.
	 */
	public function __construct() {

	}

    public function log(string $name, string $data, $element){
        if(is_object($element)){
            $elementId = $element->id;
        }
        else{
            $elementId = $element;
        }

        $log = new ApiLogModel();
        $log->name = $name;
        $log->data = $data;
        $log->elementId = $elementId;

        $this->saveLog($log);
    }


    public function getLogByElement(int $elementId){

        if (!$this->_fetchedApiByElement[$elementId]) {
            $this->_fetchedApiLogs = true;
            $results = $this->_createApiLogQuery()
                            ->where(['elementId'=>$elementId])
                            ->all();

            foreach ($results as $row) {
                $this->_fetchedApiByElement[$row['elementId']][$row['id']]  = new ApiLogModel($row);
                $this->_apiLogsById[$row['id']] = new ApiLogModel($row);
            }
        }

        return $this->_fetchedApiByElement[$elementId];
    }

    private function saveLog($logModel, $runValidation = true){
        if ($logModel->id) {
            $record = ApiLogRecord::findOne($logModel->id);

            if (!$record) {
                throw new \Exception("Log does not exist with id $logModel->id");
            }
        } else {
            $record = new ApiLogRecord();
        }

        if ($runValidation && !$record->validate()) {
            \Craft::info('Log not saved due to validation error.', __METHOD__);
            return false;
        }

        $record->name = $logModel->name;
        $record->data = $logModel->data;
        $record->elementId = $logModel->elementId;

        // Save it!
        $record->save(false);

        // Now that we have a record ID, save it on the model
        $logModel->id = $record->id;

        return true;
    }

    /**
     * Returns a Query object prepped for retrieving Countries.
     *
     * @return Query The query object.
     */
    private function _createApiLogQuery(): ActiveQuery
    {
        return ApiLogRecord::find();
    }
}